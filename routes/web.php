<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\UserDetailController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
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
    
    // 【重要】勤怠データの一括保存
    Route::post('/attendance/save-all', [AttendanceController::class, 'store'])->name('attendance.save_all');
    
    // 【修正済み】actions.js の saveBasicInfo と一致させました
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
    | 管理者専用ルート
    |----------------------------------------------------------------------
    */
    Route::middleware(['can:admin'])->group(function () {
        Route::get('/admin/menu', fn() => view('admin.selector'))->name('admin.menu');
        
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
        Route::get('/admin/register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('admin.register');
        
        // ガイドブック系
        Route::get('/manual', fn() => Inertia::render('GuideView'))->name('guidebook.show');
        Route::get('/guidebook', fn() => view('admin.guide.menu'))->name('guidebook.menu');
        Route::get('/Laravel_React_install', fn() => view('admin.guide.install'))->name('guidebook.install');
        Route::get('/Basic_detailed_design', fn() => view('admin.guide.design'))->name('guidebook.design');
        Route::get('/System_construction', fn() => view('admin.guide.construction'))->name('guidebook.construction');
    });
});

require __DIR__ . '/auth.php';