<section id="laravel">
    <h2>3. Laravelのプロジェクト作成</h2>
    <h3 id="laravel-create">3-1. プロジェクトの作成</h3>

    <p>Laravel Herdのインストールが完了したら、次はLaravelのプロジェクトを作成してみましょう。</p>
    <ol class="guide-steps">
        <li>コマンドプロンプトを開き、プログラムを保存したい場所に移動して、</br>以下のコマンドを入力してください。
            <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
                composer create-project laravel/laravel your-project-name</br>
                # プロジェクトを作成したい場所を指定する今回はデスクトップ</br>
                cd Desktop</br>
                #「your-project-name」という名前で最新のLaravelをインストール</br>
                composer create-project laravel/laravel your-project-name</br>
            </p>
        </li>
        <li>
            上記のコマンドは、Laravelの最新バージョンを「your-project-name」という名前のフォルダに<br>インストールします。<br>
            コマンドを実行すると、必要なファイルが自動的にダウンロードされ、<br>プロジェクトがセットアップされます。
        </li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image016.png') }}" alt="images/guide/install/image016.png">
        </div>
        <li>(途中省略)</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image017.png') }}" alt="images/guide/install/image017.png">
        </div>
        <li> Herdへの登録</br>
            Laravel Herdの管理画面（General > Herd paths）を開き、[Add path]を押下して作成したフォルダを追加します。
        </li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image018.png') }}" alt="images/guide/install/image018.png"></br>
            <img src="{{ asset('images/guide/install/image019.png') }}" alt="images/guide/install/image019.png"></br>
            <img src="{{ asset('images/guide/install/image020.png') }}" alt="images/guide/install/image020.png"></br>
        </div>
        <li>更に[Sites > Add]を押下します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image021.png') }}" alt="images/guide/install/image021.png">
        </div>
        <li>[Link existing project]を選択し、[Next]を押下します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image022.png') }}" alt="images/guide/install/image022.png">
        </div>
        <li>作成したフォルダを追加し、[Next]を押下します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image023.png') }}" alt="images/guide/install/image023.png"></br>
            <img src="{{ asset('images/guide/install/image024.png') }}" alt="images/guide/install/image024.png">
        </div>
        <li>最後に[Close]を押下します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image025.png') }}" alt="images/guide/install/image025.png">
        </div>
        <li>[Sites]に[your-project-name.test]が登録されたことが確認できます。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image026.png') }}" alt="images/guide/install/image026.png">
        </div>
        <li>これで http://your-project-name.test というURLで自分のPCからアクセスできるようになります。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image027.png') }}" alt="images/guide/install/image027.png">
        </div>

    </ol>
</section>
