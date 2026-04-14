<section id="remarks">
    <h2 id="remarks">6.備考</h2>
    <h3><strong>Laravelの「中に」Reactを組み込んでいる場合（Inertia.js / Breeze / Jetstream）</strong></h3>
    <p>
        もし laravel/breeze などを使ってReactをインストールした場合、</br>
        <strong>ポートに直接アクセスしても、Reactの初期画面（Viteのロゴが出る画面）は表示されません。</strong></br>
        <ul>
            <li>
                <strong>動作の仕組み:</strong></br>
                http://attendance.test (80番ポートなど) にアクセスした際、</br>LaravelがReactのコンポーネントを呼び出します。</br>
                5173 ポート（Vite）は、JavaScriptの変更をリアルタイムで反映させるための</br>「部品供給サーバー」として裏で動いているだけです。
            </li>
            <li><strong>確認方法:</strong></br>
                ブラウザでメインのURL（http://attendance.test）を開き、ログイン画面などが</br>React製になっていれば正常です。</br>
                laravelのロゴが出る画面は、（http://attendance.test/welcome）です。
            </li>
        </ul>
        <ul>

        </ul>
    </p>
</section>