<x-guest-layout>
    <!-- タイトルの設定（ブラウザタブやページ内表示用など、レイアウト側に渡すかここで直接見出しを出す場合） -->
    <div class="mb-4 text-center">
        <h2 class="text-xl font-bold text-gray-800">株式会社○○ 勤怠・経費管理システム</h2>
        <p class="text-sm text-gray-600 mt-1">ログインしてください</p>
    </div>

    <!-- セッションステータス（ログアウトメッセージ等） -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- メールアドレス -->
        <div>
            <x-input-label for="email" value="メールアドレス" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <!-- 入力ミス・エラーメッセージの表示 -->
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- パスワード -->
        <div class="mt-4">
            <x-input-label for="password" value="パスワード" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <!-- 入力ミス・エラーメッセージの表示 -->
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- ログイン保持 -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">ログイン状態を保持する</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    パスワードをお忘れですか？
                </a>
            @endif

            <x-primary-button class="ms-3">
                ログイン
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>