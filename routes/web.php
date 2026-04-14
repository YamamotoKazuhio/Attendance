<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\UserDetailController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    //return view('welcome');
    return view('top_page');
});

// 勤務表操作ガイドの表示
Route::get('/guide', function () {
    return view('guide');
})->name('guide.show');

// 経費精算操作ガイドの表示
Route::get('/guide_expense', function () {
    return view('guide_expense');
})->name('guide_expense.show');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // 0. ダッシュボード
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // 1. 勤怠管理 (Attendance)
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/save', [AttendanceController::class, 'store'])->name('attendance.save');
    Route::post('/attendance/update-basic-info', [AttendanceController::class, 'updateBasicInfo'])->name('attendance.updateBasicInfo');

    // 2. 休暇・残業申請 (Leave Applications)
    Route::post('/attendance/leave-apply', [LeaveApplicationController::class, 'store'])->name('leave.store');

    // 3. ユーザー詳細情報 (User Details)
    Route::get('/user-details/{user}', [UserDetailController::class, 'show'])->name('user_details.show');
    Route::patch('/user-details/{user}', [UserDetailController::class, 'update'])->name('user_details.update');

    // 4. 経費精算 (Expenses)
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
    | 管理者専用ルート (Admin Only)
    |----------------------------------------------------------------------
    */
    Route::middleware(['can:admin'])->group(function () {

        // 管理者メニュー選択画面
        Route::get('/admin/menu', function () {
            return view('admin.selector');
        })->name('admin.menu');

        // 勤怠：管理者用
        Route::get('/admin/monthly-report', [AttendanceController::class, 'adminMonthlyReport'])->name('admin.monthly_report');
        Route::post('/admin/finalize', [AttendanceController::class, 'finalizeMonth'])->name('admin.finalize');
        Route::post('/admin/reopen', [AttendanceController::class, 'reopenMonth'])->name('admin.reopen');

        // 経費：管理者用一覧
        Route::get('/expenses/admin', [ExpenseController::class, 'adminIndex'])->name('expenses.admin');

        // 休暇申請：承認・却下ワークフロー
        Route::get('/admin/leave-requests', [LeaveApplicationController::class, 'adminIndex'])->name('admin.leave_requests');
        Route::post('/admin/leave-requests/{leaveApplication}/approve', [LeaveApplicationController::class, 'approve'])->name('admin.leave_approve');
        Route::post('/admin/leave-requests/{leaveApplication}/reject', [LeaveApplicationController::class, 'reject'])->name('admin.leave_reject');

        // 社員情報一覧
        Route::get('/admin/users', [UserDetailController::class, 'adminIndex'])->name('admin.users.index');
        Route::delete('/admin/users/{user}', [UserDetailController::class, 'destroy'])->name('admin.users.destroy');

        // 管理者専用の登録画面表示（標準のRegisterControllerを利用）
        Route::get('/admin/register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('admin.register');

        // 論理削除からの復元
        // 削除済みのユーザーを扱うため、URLパラメータにはIDを直接指定する形式にします
        Route::post('/admin/users/{id}/restore', [UserDetailController::class, 'restore'])->name('admin.users.restore');

        // --- オートメーション ---
        // 一括チェック＆承認実行
        Route::post('/admin/monthly-report/automate', [AttendanceController::class, 'automateMonth'])->name('admin.monthly.automate');

        // 実行結果の新しいWeb画面表示
        Route::get('/admin/monthly-report/result', [AttendanceController::class, 'showAutomationResult'])->name('admin.monthly.result');

        /* マニュアル表示用ルーティング
        Route::get('/manual', function () {
            $path = public_path('pdf/kintai-app_figure.pdf');

            if (!file_exists($path)) {
                abort(404, 'マニュアルファイルが見つかりません。');
            }

            // ブラウザでそのまま表示させる設定
            return Response::file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="kintai-app_manual.pdf"'
            ]);
        })->name('manual.show');
        */

        // マニュアル表示用ルーティング
        Route::get('/manual', function () {
            return Inertia::render('GuideView'); // resources/js/Pages/GuideView.jsx
        })->name('guidebook.show');

        // 1. 構築ガイドのメインメニュー（リンク先用ページ）
        Route::get('/guidebook', function () {
            return view('admin.guide.menu'); // resources/views/admin/guide/menu.blade.php
        })->name('guidebook.menu');

        // 2. Laravel & React のインストール
        Route::get('/Laravel_React_install', function () {
            return view('admin.guide.install');
        })->name('guidebook.install');

        // 3. 基本設計・詳細設計
        Route::get('/Basic_detailed_design', function () {
            return view('admin.guide.design');
        })->name('guidebook.design');

        // 4. 業務管理システムの構築
        Route::get('/System_construction', function () {
            return view('admin.guide.construction');
        })->name('guidebook.construction');
    });
});

require __DIR__ . '/auth.php';
