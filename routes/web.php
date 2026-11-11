<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\UserDetailController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// --- SSOログイン時のリダイレクト判定 ---
Route::get('/sso-login', function (Request $request) {
    $email = $request->query('email');
    $expires = $request->query('expires');
    $signature = $request->query('signature');

    // 1. 有効期限のチェック
    if (!$expires || now()->timestamp > $expires) {
        abort(403, 'Link expired.');
    }

    // 2. 署名の検証
    $expectedSignature = hash_hmac('sha256', $email . $expires, config('app.key'));
    if (!hash_equals($expectedSignature, $signature)) {
        abort(403, 'Invalid signature.');
    }

    // 3. ユーザーの取得とログイン処理
    $user = User::with('detail')->where('email', $email)->first();

    if ($user) {
        Auth::login($user);
        $request->session()->regenerate();
        
        $role = optional($user->detail)->role;
        $is_admin = (int)$user->is_admin;

        // ログに出力してどの条件にマッチするかターミナル（storage/logs/laravel.log）で確認できるようにする
        \Illuminate\Support\Facades\Log::info("SSO判定: email={$user->email}, is_admin={$is_admin}, role={$role}");
        
        // 【要件の判定】
        // is_admin = 1 または (is_admin = 0 かつ Class_B / Class_K)
        if ($is_admin === 1 || ($is_admin === 0 && in_array($role, ['Class_B', 'Class_K']))) {
            return redirect('/admin/menu');
        }
        
        // is_admin = 0 かつ Class_NA の場合
        return redirect('/attendance');
    }

    abort(403, 'User not found.');
})->name('sso.login');

Route::get('/', fn() => view('top_page'));
Route::get('/welcome', fn() => view('welcome'));
Route::get('/guide', fn() => view('guide'))->name('guide.show');
Route::get('/guide_expense', fn() => view('guide_expense'))->name('guide_expense.show');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // 0. ダッシュボード
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // 1. 勤怠管理 (Attendance)
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/save-all', [AttendanceController::class, 'store'])->name('attendance.save_all');
    Route::post('/attendance/save-basic-info', [AttendanceController::class, 'updateBasicInfo'])->name('attendance.save_basic_info');

    // 2. 休暇申請管理
    Route::post('/attendance/leave-apply', [LeaveApplicationController::class, 'store'])->name('leave.store');

    // 3. ユーザー詳細情報
    Route::get('/user-details/{user}', [UserDetailController::class, 'show'])->name('user_details.show');
    Route::patch('/user-details/{user}', [UserDetailController::class, 'update'])->name('user_details.update');

    // 4. 経費精算
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::patch('/expenses/{expense}/status', [ExpenseController::class, 'updateStatus'])->name('expenses.status');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // 5. プロフィール
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |----------------------------------------------------------------------
    | 管理者・マネージャー（Class_B, Class_K）専用ルート
    |----------------------------------------------------------------------
    */
    Route::middleware(['auth'])->group(function () {
        
        // 管理メニュー画面のアクセスガード
        Route::get('/admin/menu', function() {
            $user = Auth::user();
            $role = optional($user->detail)->role;
            
            // is_admin = 1 または (is_admin = 0 かつ Class_B / Class_K) のみ許可
            $canAccess = $user->is_admin == 1 || ($user->is_admin == 0 && in_array($role, ['Class_B', 'Class_K']));
            
            if (!$canAccess) {
                abort(403, '管理者メニューへのアクセス権限がありません。');
            }
            
            return view('admin.selector');
        })->name('admin.menu');
        
        // 勤怠レポート・自動化
        Route::get('/admin/monthly-report', [AttendanceController::class, 'adminMonthlyReport'])->name('admin.monthly_report');
        Route::post('/admin/finalize', [AttendanceController::class, 'finalizeMonth'])->name('admin.finalize');
        Route::post('/admin/reopen', [AttendanceController::class, 'reopenMonth'])->name('admin.reopen');
        Route::post('/admin/monthly-report/automate', [AttendanceController::class, 'automateMonth'])->name('admin.monthly.automate');
        Route::get('/admin/monthly-report/result', [AttendanceController::class, 'showAutomationResult'])->name('admin.monthly.result');

        // 勤怠承認ステータスの切り替え
        Route::post('/admin/attendance/toggle-status/{id}', [AttendanceController::class, 'toggleStatus'])->name('admin.attendance.toggle');
        
        // 休暇申請の管理
        Route::get('/admin/leave-requests', [LeaveApplicationController::class, 'adminIndex'])->name('admin.leave_requests');
        Route::post('/admin/leave-requests/{leaveApplication}/approve', [LeaveApplicationController::class, 'approve'])->name('admin.leave_approve');
        Route::post('/admin/leave-requests/{leaveApplication}/reject', [LeaveApplicationController::class, 'reject'])->name('admin.leave_reject');
        
        // 経費・ユーザー管理
        Route::get('/expenses/admin', [ExpenseController::class, 'adminIndex'])->name('expenses.admin');
        Route::get('/admin/users', [UserDetailController::class, 'adminIndex'])->name('admin.users.index');
        Route::delete('/admin/users/{user}', [UserDetailController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/admin/users/{id}/restore', [UserDetailController::class, 'restore'])->name('admin.users.restore');
        Route::get('/admin/users/create', [UserDetailController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users/store', [UserDetailController::class, 'storeByAdmin'])->name('admin.users.store');
        
        // ガイドブック系
        Route::get('/manual', fn() => Inertia::render('GuideView'))->name('guidebook.show');
        Route::get('/guidebook', fn() => view('admin.guide.menu'))->name('guidebook.menu');
        Route::get('/Laravel_React_install', fn() => view('admin.guide.install'))->name('guidebook.install');
        Route::get('/Basic_detailed_design', fn() => view('admin.guide.design'))->name('guidebook.design');
        Route::get('/System_construction', fn() => view('admin.guide.construction'))->name('guidebook.construction');
    });
});

require __DIR__ . '/auth.php';

// --- ログアウト処理 ---
Route::match(['get', 'post'], '/logout', function (Request $request) {
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');