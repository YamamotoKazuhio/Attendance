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
        // 1. 認証を実行
        $request->authenticate();
    
        // 2. セッションを再生成
        $request->session()->regenerate();

        // 3. 権限に応じてリダイレクト先を振り分け
        // 管理者の場合
        if ($request->user()->is_admin) {
            return redirect()->route('admin.menu');
        }

        // 一般ユーザーの場合
        // intended() を使用することで、ログイン前にアクセスしようとしていたURLがある場合はそこへ、
        // なければ指定したルート（attendance.index）へ遷移します
        return redirect()->intended(route('attendance.index', absolute: false));
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
