<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // ログイン済みの場合の振り分け
                if (Auth::user()->is_admin) {
                    return redirect()->route('admin.menu');
                }
                return redirect()->route('attendance.index');
            }
        }

        return $next($request);
    }
}