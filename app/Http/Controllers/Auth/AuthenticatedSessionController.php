<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // ログインしたユーザーと詳細情報（ロール・部署）を取得
        $user = $request->user();
        $user->load('detail');
        
        $role = optional($user->detail)->role;
        $department = optional($user->detail)->department; 
        $is_admin = (int)$user->is_admin;

        // 総務部の場合の判定
       if ($department === '総務部') {
            return redirect()->intended(route('soumu.menu'));
        }

        // 管理者（is_admin = 1）またはロールが Class_B / Class_K の場合は管理者メニューへ
        if ($is_admin === 1 || in_array($role, ['Class_B', 'Class_K'])) {
            return redirect()->intended(route('admin.menu'));
        }
    
        // 一般ユーザー（Class_NA等）の場合は勤務表画面へ
        return redirect()->intended(route('attendance.index'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}