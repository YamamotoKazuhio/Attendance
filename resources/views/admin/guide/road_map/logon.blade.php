<section>
    <h2 id="logon">2. Laravel Breeze（Starter Kit）でログイン機能を構築</h2>
    <p>
        Laravel Breeze（Starter Kit）を使用して、EC2上の既存プロジェクトにログイン機能を組み込む手順をまとめます。</br>
        Breezeは、ログイン、登録、パスワードリセットなどの認証機能を一括で生成してくれる非常に便利なツールです。</br>
        Reactを使用されているとのことですので、Inertia.js を使ったスタックが最適です。
    </p>

    <h3 id="install-breeze">2-1. Laravel Breeze（Starter Kit）の導入</h3>
    <ol>
        <li>Breezeのインストール（Composer）
            <p>
                まず、プロジェクトのルートディレクトリでBreezeパッケージをダウンロードします。<br>
                Bash</br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code># Laravel Breezeのインストール
composer require laravel/breeze --dev</code></pre>
            </p>
        </li>

        <li>Breezeのセットアップ（React選択）
            <p>
                次に、Breezeのインストールコマンドを実行します。対話形式、またはオプションで設定を選びます。<br>
                Bash</br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code># Laravel Breezeのセットアップ（React選択）
php artisan breeze:install react</code></pre>
                <ul>
                    <li>Dark mode: 任意（yes/no）</li>
                    <li>Unit testing: 任意（PHPUnit/Pest）</br>
                        このコマンドを実行すると、以下のファイルが自動生成されます：
                        <ul>Dark mode: 任意（yes/no）</ul>
                        <ul>Unit testing: 任意（PHPUnit/Pest）</ul>
                        <ul>Routes: routes/auth.php（ログイン等のルーティング）</ul>
                    </li>
                </ul>
            </p>
        </li>

        <li>データベースのマイグレーション
            <p>
                認証に必要な users テーブルなどを作成（または更新）します。<br>
                Bash</br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code># データベースのマイグレーション
php artisan migrate</code></pre>
                <ul>
                    注意：</br>
                    すでにテーブルがある場合はエラーになることがあります。</br>
                    その場合イグレーションファイルを確認してください。
                </ul>
            </p>
        </li>

        <li>フロントエンドのビルド
            <p>
               Breezeが生成した新しいReactコンポーネントをコンパイルします。<br>
                Bash</br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code># フロントエンドのビルド
npm install
npm run build</code></pre>
            </p>
        </li>

        <li>ルーティングの確認
            <p>
                Breezeをインストールすると、routes/web.php にダッシュボードへのルートが自動追記されます。<br>
                PHP
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>// routes/web.php
use Illuminate\Support\Facades\Route;
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';</code></pre>
            </p>
        </li>
    </ol>

    <h2 id="caution-EC2">2-2. EC2環境で特に注意すべき点</h2>
    <ol>
        <li>Viteの実行
            <p>
                EC2上で開発を続ける場合、npm run dev ではなく、本番公開用として npm run build を実行した状態にしてください。<br>
                これにより public/build 内に正規のファイルが書き出されます。
            </p>
        </li>

        <li>環境変数（.env）のAPP_URL
            <p>
                .env ファイルの APP_URL が、現在のEC2のパブリックIPアドレス（またはドメイン）になっているか確認してください。</br>
                ここが localhost のままだと、リダイレクトが正しく動作しないことがあります。<br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>コード スニペット
APP_URL=http://パブリック IPv4 アドレス</code></pre>
            </p>
        </li>

        <li>フォルダ権限の再確認
            <p>
                Breezeのインストールで新しいファイルが増えるため、再度Apacheが書き込み・読み込みを行えるよう権限を整えておくと安全です。<br>
                Bash</br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code># フォルダ権限の再確認
sudo chown -R ec2-user:apache storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache</code></pre>
            </p>
        </li>
    </ol>

    <h2 id="tinker">2-3. php artisan tinkerの利用（管理者の作成）</h2>
    <p>
        最初にご案内した php artisan tinker を使って、このBreezeで作られた users テーブルに管理者ユーザーを登録すれば、</br>
        http://IPaddress/login からログインできるようになります。<br>
        基本的な使い方と、開発でよく使うテクニックをまとめました。
    </p>
    <ol>
        <li>起動と終了
            <p>
                プロジェクトのルートディレクトリで以下のコマンドを実行します。<br>
                Bash</br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code># Tinkerの起動
php artisan tinker</code></pre>
                <ul>
                    • 終了方法: exit または q と入力してEnter、もしくは Ctrl + C で抜けられます。</br>
                    • 再起動: コードを修正した後は、exit してから再度 tinker を起動しないと、修正内容が反映されないので注意してください。
                </ul>
            </p>
        </li>

        <li>よく使う操作例
            <p>
                データベース（Eloquent）の操作や、ヘルパー関数の動作確認によく使われます。<br>
                モデルの操作<br>
                PHP<br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>// 全ユーザーを取得
$users = App\Models\User::all();

// 特定のIDのユーザーを検索
$user = App\Models\User::find(1);

// データの作成
App\Models\User::create(['name' => 'Test User', 'email' => 'test@example.com', 'password' => bcrypt('password')]);

// データの更新
$user->name = 'Updated Name';
$user->save();</code></pre>
                ヘルパー関数やコレクションの確認<br>
                PHP<br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>// 文字列操作の確認
Str::slug('Laravel Artisan Tinker'); // "laravel-artisan-tinker"

// コレクションの動作確認
collect([1, 2, 3])->map(fn($n) => $n * 2);</code></pre>
            </p>
        </li>

        <li>知っておくと便利なコマンド
            <p>Tinker内では、PHPのコード以外にも便利な専用コマンドが使えます。</p>
            <table>
                <tr>
                    <th>コマンド</th>
                    <th>説明</th>
                </tr>
                <tr>
                    <td>ls</td>
                    <td>変数、メソッド、定数の一覧を表示</td>
                </tr>
                <tr>
                    <td>doc &lt;クラス名 /関数&gt;</td>
                    <td>ドキュメント（PHPDoc）を表示</td>
                </tr>
                <tr>
                    <td>show &lt;クラス名 /関数&gt;</td>
                    <td>そのコードの定義内容を画面に表示</td>
                </tr>
                <tr>
                    <td>wtf</td>
                    <td>直前に発生した例外のスタックトレースを表示</td>
                </tr>
                <tr>
                    <td>history</td>
                    <td>実行したコマンドの履歴を表示</td>
                </tr>
            </table>
        </li>

        <li>活用のポイント
            <p>
                <ul><strong>名前空間の省略：</strong></br>
                    Laravel 8以降であれば、App\Models\User を単にUserと入力するだけで解決してくれる</br>
                    「クラスエイリアス」が標準で効くようになっています。
                </ul>
                <ul><strong>テストデータの作成：</strong><br>
                    User::factory()->count(5)->create() のように実行して、</br>
                    手軽にDBをテストデータで満たすことができます。
                </ul>
                 <ul><strong>パスワードのデバッグ：</strong><br>
                    「ログインできない」といった際に、Hash::check('password', $user->password) を実行して</br>
                    パスワードの整合性を確認する際などに重宝します。
                </ul>
                <br>
                もし特定の機能（AWS連携や独自ライブラリなど）の動作をTinkerで試したい場合は、</br>具体的なクラス名などを教えていただければ、そのコード例も提示できます。
            </p>
        </li>       
    </ol>
</section>