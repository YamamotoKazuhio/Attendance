<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\UserDetailController;
use App\Http\Controllers\PayrollController;
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
        $department = optional($user->detail)->department; // 部署を取得
        $is_admin = (int)$user->is_admin;

        // ログに出力してどの条件にマッチするかターミナル（storage/logs/laravel.log）で確認できるようにする
        \Illuminate\Support\Facades\Log::info("SSO判定: email={$user->email}, dept={$department}, is_admin={$is_admin}, role={$role}");
        
        // 【最優先】総務部かつ Class_B または Class_K の場合は必ず総務部メニューへ
        if ($department === '総務部' && in_array($role, ['Class_B', 'Class_K'])) {
            return redirect('/soumu/menu');
        }

        // 【条件2】is_admin = 1 の場合（総務部の Class_B/K 以外で管理者権限を持つ人）
        if ($is_admin === 1) {
            return redirect('/admin/menu');
        }
        
        // 【条件3】総務部のその他の場合（Class_NA など）
        if ($department === '総務部') {
            return redirect('/payment/admin');
        }
        
        // 【条件4】is_admin = 0 かつ総務部以外の Class_B / Class_K の場合
        if ($is_admin === 0 && in_array($role, ['Class_B', 'Class_K'])) {
            return redirect('/admin/menu');
        }
        
        // 【条件5】一般ユーザー
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
| Authenticated Routes (すべての認証済みユーザー共通)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // 0. ダッシュボード
    Route::get('/dashboard', function () {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        // ユーザー詳細をロード
        $user->load('detail');
        $role = optional($user->detail)->role;
        $department = optional($user->detail)->department;
        $is_admin = (int)$user->is_admin;

        \Illuminate\Support\Facades\Log::info("ローカルログイン判定: email={$user->email}, dept={$department}, is_admin={$is_admin}, role={$role}");

        // 【最優先】総務部かつ Class_B または Class_K の場合は必ず総務部メニューへ
        if ($department === '総務部' && in_array($role, ['Class_B', 'Class_K'])) {
            return redirect('/soumu/menu');
        }

        // 【条件2】is_admin = 1 の場合
        if ($is_admin === 1) {
            return redirect('/admin/menu');
        }
        
        // 【条件3】総務部のその他の場合（Class_NA など）
        if ($department === '総務部') {
            return redirect('/payment/admin');
        }
        
        // 【条件4】is_admin = 0 かつ総務部以外の Class_B / Class_K の場合
        if ($is_admin === 0 && in_array($role, ['Class_B', 'Class_K'])) {
            return redirect('/admin/menu');
        }
        
        // 【条件5】一般ユーザー
        return redirect('/attendance');
    })->name('dashboard');

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

    // 管理メニュー画面
    Route::get('/admin/menu', function() {
        return view('admin.selector');
    })->name('admin.menu');

    // 総務部専用メニュー画面
    Route::get('/soumu/menu', function() {
        $user = Auth::user();
        $department = optional($user->detail)->department;
        if ($department !== '総務部' && $user->is_admin != 1) {
            abort(403, '総務部専用メニューへのアクセス権限がありません。');
        }
        return view('soumu.menu');
    })->name('soumu.menu');
    
    // 勤怠レポート・自動化
    Route::get('/admin/monthly-report', [AttendanceController::class, 'adminMonthlyReport'])->name('admin.monthly_report');
    Route::post('/admin/finalize', [AttendanceController::class, 'finalizeMonth'])->name('admin.finalize');
    Route::post('/admin/reopen', [AttendanceController::class, 'reopenMonth'])->name('admin.reopen');
    Route::post('/admin/monthly-report/automate', [AttendanceController::class, 'automateMonth'])->name('admin.monthly.automate');
    Route::get('/admin/monthly-report/result', [AttendanceController::class, 'showAutomationResult'])->name('admin.monthly.result');

    // 勤怠承認ステータスの切り替え
    Route::post('/admin/attendance/toggle-status/{id?}', [AttendanceController::class, 'toggleStatus'])->name('admin.attendance.toggle');
    Route::post('/admin/attendance/toggle-status-alias/{id?}', [AttendanceController::class, 'toggleStatus'])->name('admin.attendance.toggle-status');
    
    // 休暇申請の管理
    Route::get('/admin/leave-requests', [LeaveApplicationController::class, 'adminIndex'])->name('admin.leave_requests');
    Route::post('/admin/leave-requests/{leaveApplication}/approve', [LeaveApplicationController::class, 'approve'])->name('admin.leave_approve');
    Route::post('/admin/leave-requests/{leaveApplication}/reject', [LeaveApplicationController::class, 'reject'])->name('admin.leave_reject');
    
    // 経費・ユーザー管理
    Route::get('/expenses/admin', [ExpenseController::class, 'adminIndex'])->name('expenses.admin');
    
    // 支払管理メニュー画面のルート
    Route::get('/payment/admin', function() {
        return view('payment.admin');
    })->name('payment.admin');

    // 給与管理画面のルート
    Route::get('/payment/payroll', [PayrollController::class, 'index'])->name('payment.payroll');

    // 給与設定画面のルート
    Route::get('/payment/settings', [PayrollController::class, 'settingsIndex'])->name('payment.settings');
    Route::post('/payment/settings', [PayrollController::class, 'settingsUpdate'])->name('payment.settings.update');

    // 経費支払管理画面のルート
    Route::get('/payment/expenses', [ExpenseController::class, 'paymentAdminIndex'])->name('payment.expenses.index');

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

require __DIR__ . '/auth.php';

// --- ログアウト処理 ---
Route::match(['get', 'post'], '/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');