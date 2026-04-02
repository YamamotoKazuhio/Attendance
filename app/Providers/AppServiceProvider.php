<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 'admin' という名前の権限を定義
        Gate::define('admin', function (User $user) {
            // boolへのキャスト、または緩い比較を使用することで、"1" も 1 も true と判定させます
            return $user->is_admin === true;
        });
    }
}
