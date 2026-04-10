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
            <img src="{{ asset('images/guide/install/image028.png') }}" alt="MySQL Installer画面2">
            <p class="caption">図3.11：MySQL Installer画面1</p>
        </div>
        <li>[No thanks, just start my download.]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image029.png') }}" alt="MySQL Installer画面2">
            <p class="caption">図3.11：MySQL Installer画面2</p>
        </div>
        <li> 自PCのダウンロードフォルダに[mysql-installer-web-community-8.X.XX.X.msi]ファイルが生成するので、これをクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image030.png') }}" alt="MySQL Installer画面4">
            <p class="caption">図3.13：MySQL Installer画面3</p>
        </div>
        <li>[Server only]を選択し、[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image031.png') }}" alt="MySQL Installer画面5">
            <p class="caption">図3.14：MySQL Installer画面4</p>
        </div>
         <li>[MySQL Server 8.x.xx]を選択し、[Execute]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image032.png') }}" alt="MySQL Installer画面6">
            <p class="caption">図3.15：MySQL Installer画面5</p>
        </div>
        <li>しばらく待ちます。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image033.png') }}" alt="MySQL Installer画面7">
            <p class="caption">図3.16：MySQL Installer画面6</p>
        </div>
        <li>[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image034.png') }}" alt="MySQL Installer画面8">
            <p class="caption">図3.17：MySQL Installer画面7</p>
        </div>
        <li>[Execute]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image035.png') }}" alt="MySQL Installer画面9">
            <p class="caption">図3.18：MySQL Installer画面8</p>
        </div>
        <li>[Next>]をクリックします。</li>  
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image036.png') }}" alt="MySQL Installer画面10">
            <p class="caption">図3.19：MySQL Installer画面9</p>
        </div>
        <li>[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image037.png') }}" alt="MySQL Installer画面11">
            <p class="caption">図3.20：MySQL Installer画面10</p>
        </div>
        <li>そのまま[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image038.png') }}" alt="MySQL Installer画面12">
            <p class="caption">図3.21：MySQL Installer画面11</p>
        </div>
        <li>そのまま[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image039.png') }}" alt="MySQL Installer画面13">
            <p class="caption">図3.22：MySQL Installer画面12</p>
        </div>
        <li>MySQLのパスワードを入力し、[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image040.png') }}" alt="MySQL Installer画面14">
            <p class="caption">図3.23：MySQL Installer画面13</p>
        </div>
        <li>そのまま[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image041.png') }}" alt="MySQL Installer画面15">
            <p class="caption">図3.24：MySQL Installer画面14</p>
        </div>
        <li>そのまま[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image042.png') }}" alt="MySQL Installer画面16">
            <p class="caption">図3.25：MySQL Installer画面15</p>
        </div>
        <li>[Execute]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image043.png') }}" alt="MySQL Installer画面17">
            <p class="caption">図3.26：MySQL Installer画面16</p>
        </div>
        <li>しばらく待ちます。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image044.png') }}" alt="MySQL Installer画面18">
            <p class="caption">図3.27：MySQL Installer画面17</p>
        </div>
        <li>[Finish]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image045.png') }}" alt="MySQL Installer画面19">
            <p class="caption">図3.28：MySQL Installer画面18</p>
        </div>
        <li>[Next>]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image046.png') }}" alt="MySQL Installer画面20">
            <p class="caption">図3.29：MySQL Installer画面19</p>
        </div>
        <li>[Finish]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image047.png') }}" alt="MySQL Installer画面21">
            <p class="caption">図3.30：MySQL Installer画面20</p>
        </div>
        <li>MySQL Installerを閉じます。</li>

    </ol>

    <h3 id="mysql-config">4-3. .envファイルを書き換える</h3>
    <ol class="guide-steps">
        <li>MySQLサーバーが準備できたら、VSCordなどを利用して.env ファイルを「SQLite（ファイル形式）」から「MySQL（サーバー形式）」へ切り替えます。</li>
        <div class="guide-figure">  
            <img src="{{ asset('images/guide/install/image048.png') }}" alt="MySQLへの切り替え画面1">
            <p class="caption">図3.31：MySQLへの切り替え画面1</p>
        </div>
    </ol>

    <h3 id="mysql-env">4-4.環境変数の設定</h3>
    <ol class="guide-steps">
        <li>キーボードの Windowsキー を押し、「環境変数」と入力して「システム環境変数の編集」を開きます。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image049.png') }}" alt="環境変数の編集画面1">
            <p class="caption">図3.32：環境変数の編集画面1</p>
        </div>
        <li>[環境変数(N)...]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image050.png') }}" alt="環境変数の編集画面2">
            <p class="caption">図3.33：環境変数の編集画面2</p>
        </div>
        <li>[システム環境変数(S)]の[Path]を選択し、[編集(E)...]をクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image051.png') }}" alt="環境変数の編集画面3">
            <p class="caption">図3.34：環境変数の編集画面3</p>
        </div>
        <li>[新規(N)]をクリックし、MySQLのbinフォルダのパス「C:\Program Files\MySQL\MySQL Server 8.0\bin」を入力します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image052.png') }}" alt="環境変数の編集画面4">
            <p class="caption">図3.35：環境変数の編集画面4</p>
        </div>
        <li>[OK]をクリックして、すべてのウィンドウを閉じます。</li>
        <li>重要：今開いているコマンドプロンプトを一度閉じ、新しく開き直します。</li>
    </ol>

    <h3 id="mysql-create">4-5. データベースの作成</h3>
    <ol class="guide-steps">
        <li>コマンドプロント等を開き、mysql -u root -pと入力しEnterキーを押下します。</br>
            その後、DBのパスワードを入力することでmysqlが起動します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image053.png') }}" alt="DB画面1">
            <p class="caption">図3.36：DB画面1</p>
        </div>
        <li>ログインできたら、Laravel用のデータベースを作成します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image054.png') }}" alt="DB画面2">
            <p class="caption">図3.37：DB画面2</p>
            <img src="{{ asset('images/guide/install/image055.png') }}" alt="DB画面3">
            <p class="caption">図3.38：DB画面3</p>
        </div>
        <li>DBの作成ができたら、以下のコマンドをプロジェクトのフォルダで実行します。</li>
        <pre class="code-block">php artisan migrate</pre>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image056.png') }}" alt="DB画面4">
            <p class="caption">図3.39：DB画面4</p>  
    </ol>

</section>
