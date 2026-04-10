<section id="laravel">
    <h2>3. Laravelのプロジェクト作成</h2>
    <h3 id="laravel-create">3-1. プロジェクトの作成</h3>

    <p>Laravel Herdのインストールが完了したら、次はLaravelのプロジェクトを作成してみましょう。</p>
    <ol class="guide-steps">
        <li>コマンドプロンプトを開き、プログラムを保存したい場所に移動して、以下のコマンドを入力してください。</li>
        <pre class="code-block">composer create-project laravel/laravel your-project-name</pre>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image015.png') }}" alt="コマンド実行画面">
            <p class="caption">図3.1：Laravelプロジェクト作成コマンド画面</p>
        </div>
        <li>
            上記のコマンドは、Laravelの最新バージョンを「your-project-name」という名前のフォルダにインストールします。<br>
            コマンドを実行すると、必要なファイルが自動的にダウンロードされ、プロジェクトがセットアップされます。
        </li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image016.png') }}" alt="Laravelプロジェクト作成画面1">
            <p class="caption">図3.1：Laravelプロジェクト作成画面1</p>
        </div>
        <li>(途中省略)</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image017.png') }}" alt="Laravelプロジェクト作成画面2">
            <p class="caption">図3.1：Laravelプロジェクト作成画面2</p>
        </div>
        <li> Herdへの登録</br>
            Laravel Herdの管理画面（General > Herd paths）を開き、[Add path]を押下して作成したフォルダを追加します。
        </li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image018.png') }}" alt="Herdへの登録画面1">
            <p class="caption">図3.2：Herdへの登録画面1</p>
            <img src="{{ asset('images/guide/install/image019.png') }}" alt="Herdへの登録画面2">
            <p class="caption">図3.3：Herdへの登録画面2</p>
            <img src="{{ asset('images/guide/install/image020.png') }}" alt="Herdへの登録画面3">
            <p class="caption">図3.4：Herdへの登録画面3</p>
        </div>
        <li>更に[Sites > Add]を押下します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image021.png') }}" alt="Herdへの登録画面4">
            <p class="caption">図3.4：Herdへの登録画面4</p>
        </div>
        <li>[Link existing project]を選択し、[Next]を押下します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image022.png') }}" alt="Herdへの登録画面5">
            <p class="caption">図3.5：Herdへの登録画面5</p>
        </div>
        <li>作成したフォルダを追加し、[Next]を押下します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image023.png') }}" alt="Herdへの登録画面6">
            <p class="caption">図3.6：Herdへの登録画面6</p>
            <img src="{{ asset('images/guide/install/image024.png') }}" alt="Herdへの登録画面7">
            <p class="caption">図3.7：Herdへの登録画面7</p>
        </div>
        <li>最後に[Close]を押下します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image025.png') }}" alt="Herdへの登録画面8">
            <p class="caption">図3.8：Herdへの登録画面8</p>
        </div>
        <li>[Sites]に[your-project-name.test]が登録されたことが確認できます。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image026.png') }}" alt="Herdへの登録画面9">
            <p class="caption">図3.9：Herdへの登録画面9</p>
        </div>
        <li>これで http://your-project-name.test というURLで自分のPCからアクセスできるようになります。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image027.png') }}" alt="Herdへの登録画面10">
            <p class="caption">図3.10：Herdへの登録画面10</p>
        </div>

    </ol>
</section>
