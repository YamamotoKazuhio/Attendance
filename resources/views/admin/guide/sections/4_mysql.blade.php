<section id="mysql">
    <h2>4. MySQLのインストール</h2>
    <h3 id="mysql-download">4-1. MySQLのダウンロード</h3>
    <p>
        Laravel Herdの無料版は、PHPの環境は作ってくれますが、MySQL（データベース本体）は同梱されていません。そこで、MySQLの代わりにデフォルトDBとしSQLiteが使われています。</br>
        SQLiteではなく、MySQLを利用するためには、別途 準備する必要があります。<br>
        本節では、MySQLを利用するための準備する手順について記述します。
    </p>

    <h3 id="mysql-install">4-2. MySQLのインストール</h3>
    <ol class="guide-steps">
        <li><a href="https://dev.mysql.com/downloads/installer/" target="_blank">MySQL Installer</a>を開きます。</li>
        <li>Select VersionとSelect Operating Systemを適宜選択し、[Download]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image028.png') }}" alt="images/guide/install/image028.png">
        </div>
        <li>[No thanks, just start my download.]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image029.png') }}" alt="images/guide/install/image029.png">
        </div>
        <li> 自PCのダウンロードフォルダに[mysql-installer-web-community-8.X.XX.X.msi]ファイルが生成するので、これをクリックします。</li>
        <div>
            <img src="{{ asset('images/guide/install/image030.png') }}" alt="images/guide/install/image030.png">
        </div>
        <li>[Server only]を選択し、[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image031.png') }}" alt="images/guide/install/image031.png">
        </div>
         <li>[MySQL Server 8.x.xx]を選択し、[Execute]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image032.png') }}" alt="images/guide/install/image032.png">
        </div>
        <li>しばらく待ちます。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image033.png') }}" alt="images/guide/install/image033.png'">
        </div>
        <li>[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image034.png') }}" alt="images/guide/install/image034.png">
        </div>
        <li>[Execute]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image035.png') }}" alt="images/guide/install/image035.png">
        </div>
        <li>[Next>]をクリックします。</li>  
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image036.png') }}" alt="images/guide/install/image036.png">
       </div>
        <li>[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image037.png') }}" alt="images/guide/install/image037.png">
        </div>
        <li>そのまま[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image038.png') }}" alt="images/guide/install/image038.png">
        </div>
        <li>そのまま[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image039.png') }}" alt="images/guide/install/image039.png">
        </div>
        <li>MySQLのパスワードを入力し、[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image040.png') }}" alt="images/guide/install/image040.png">
        </div>
        <li>そのまま[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image041.png') }}" alt="images/guide/install/image041.png">
        </div>
        <li>そのまま[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image042.png') }}" alt="images/guide/install/image042.png">
        </div>
        <li>[Execute]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image043.png') }}" alt="images/guide/install/image043.png">
        </div>
        <li>しばらく待ちます。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image044.png') }}" alt="images/guide/install/image044.png">
          </div>
        <li>[Finish]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image045.png') }}" alt="images/guide/install/image045.png">
        </div>
        <li>[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image046.png') }}" alt="images/guide/install/image046.png">
        </div>
        <li>[Finish]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image047.png') }}" alt="images/guide/install/image047.png">
        </div>
        <li>MySQL Installerを閉じます。</li>

    </ol>

    <h3 id="mysql-config">4-3. .envファイルを書き換える</h3>
    <ol class="guide-steps">
        <li>
            MySQLサーバーが準備できたら、VSCordなどを利用して.env ファイルを「SQLite（ファイル形式）」から「MySQL（サーバー形式）」へ切り替えます。
            <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
                # 修正前：DB_CONNECTION=sqlite</br>
                </br>
                # 修正後：</br>
                DB_CONNECTION=mysql</br>
                DB_HOST=127.0.0.1</br>
                DB_PORT=3306</br>
                DB_DATABASE=kintai  # 自分で作成したDB名</br>
                DB_USERNAME=root       # 設定したユーザー名（通常はroot）</br>
                DB_PASSWORD=root       # 設定したパスワード（空の場合もあり）
            </p>
        </li>
        
    </ol>

    <h3 id="mysql-env">4-4.環境変数の設定</h3>
    <ol class="guide-steps">
        <li>キーボードの Windowsキー を押し、「環境変数」と入力して「システム環境変数の編集」を開きます。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image049.png') }}" alt="images/guide/install/image049.png">
        </div>
        <li>[環境変数(N)...]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image050.png') }}" alt="images/guide/install/image050.png">
        </div>
        <li>[システム環境変数(S)]の[Path]を選択し、[編集(E)...]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image051.png') }}" alt="images/guide/install/image051.png">
        </div>
        <li>[新規(N)]をクリックし、MySQLのbinフォルダのパス「C:\Program Files\MySQL\MySQL Server 8.0\bin」を入力します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image052.png') }}" alt="images/guide/install/image052.png">
        </div>
        <li>[OK]をクリックして、すべてのウィンドウを閉じます。</li>
        <li>重要：今開いているコマンドプロンプトを一度閉じ、新しく開き直します。</li>
    </ol>

    <h3 id="mysql-create">4-5. データベースの作成</h3>
    <ol class="guide-steps">
        <li>コマンドプロント等を開き、mysql -u root -pと入力しEnterキーを押下します。</br>
            その後、DBのパスワードを入力することでmysqlが起動します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image053.png') }}" alt="images/guide/install/image053.png">
        </div>
        </li>
        <li>
            ログインできたら、Laravel用のデータベースを作成します。</br>
            <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
                -- MySQLにログインした状態で実行</br>
                CREATE DATABASE kintai_app_db;
            </p></br>
            <div class="guide-figure">
                <img src="{{ asset('images/guide/install/image054.png') }}" alt="images/guide/install/image054.png">
            </div>
        </li> 

        <li>quitと入力し、DBから抜けます。
        <div class="guide-figure">
            <br>
            <img src="{{ asset('images/guide/install/image055.png') }}" alt="images/guide/install/image055.png">
        </div>
        <li>DBの作成ができたら、以下のコマンドをプロジェクトのフォルダで実行します。</li>
        <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
            php artisan migrate
        </p>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image056.png') }}" alt="images/guide/install/image056.png">
    </ol>

</section>
