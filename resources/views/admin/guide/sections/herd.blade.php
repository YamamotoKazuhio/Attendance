<section id="herd">
    <h2>2. Laravel Herd のインストール</h2>
    <h3 id="herd-setup">2-1. セットアップ</h3>
    <p>
        WindowsでLaravel開発を始めるなら、以前は「XAMPP（ザンプ）」などが定番でしたが、<br>
        現在は「Laravel Herd（ハード）」というツールを使うのが最も簡単でスマートです。<br>
        本節では、Laravel Herdのセットアップ手順について記述します。
    </p>

    <ol class="guide-steps">
        <li>Laravel Herd 公式サイト（<a href="https://herd.laravel.com/" target="_blank">https://herd.laravel.com/</a>）にアクセスします。</li>
        <li>「Download for Windows」ボタンをクリックしてインストーラーをダウンロードし、実行します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image001.png') }}" alt="Herdダウンロード画面">
            <p class="caption">図2.1：Herd公式サイトのダウンロード画面</p>
        </div>
        <li>Herdのダウンロードが開始されます。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image002.png') }}" alt="Herdダウンロード画面">
            <p class="caption">図2.1：Herd公式サイトのダウンロード画面</p>
        </div>
        <li>ダウンロードフォルダに[Herd-1.25.0-setup.exe]ファイルが生成されるので、これをクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image003.png') }}" alt="Herdダウンロード開始画面">
            <p class="caption">図2.2：ダウンロード開始画面</p>
        </div>
        <li>[Herd]のインストールが開始されます。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image004.png') }}" alt="Herdインストール画面">
            <p class="caption">図2.3：Herdインストール画面</p>
        </div>
        <li>以下の表示になれば、インストールは完了です。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image005.png') }}" alt="Herd完了画面">
            <p class="caption">図2.4：Herd完了画面</p>
        </div>
        <li>Windowsメニュー[タスクメニュー]よりHerdのアイコンをクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image006.png') }}" alt="Herd起動画面">
            <p class="caption">図2.5：Herd起動画面</p>
        </div>
        <li>以下のような画面が表示されることを確認します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image007.png') }}" alt="Herd画面">
            <p class="caption">図2.6：Herd画面</p>
        </div>
        <li>インストールが完了すると、自動的に PHP と Composer がPCで使えるようになります。</br>
            正しく入ったか、コマンドプロンプトを開いて以下のコマンドを打ってみてください。
            <pre class="code-block">php -v</br>composer -V</pre>
          </li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image008.png') }}" alt="Herdバージョン確認画面">
            <p class="caption">図2.7：Herdバージョン確認画面</p>
        </div>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image009.png') }}" alt="Herdバージョンコマンド確認画面">
            <p class="caption">図2.8：Herdバージョンコマンド確認画面</p>
        </div>
    </ol>

    <h3 id="herd-config">2-2. パス（Path）を登録する手順</h3>
    <p>以下の手順で、HerdのPHPをPCのメインに設定します。</p>
    <ol class="guide-steps">
        <li>Windowsのスタートメニューから「環境変数」と検索し、「システム環境変数を編集」を選択します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image010.png') }}" alt="環境変数検索画面">
            <p class="caption">図2.9：環境変数検索画面</p>
        </div>
        <li>「システムのプロパティ」ウィンドウが開いたら、下部の「環境変数(N)...」ボタンをクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image011.png') }}" alt="環境変数ウィンドウ画面">
            <p class="caption">図2.10：環境変数ウィンドウ画面</p>
        </div>
        <li>「システム環境変数」のリストから「Path」を選択し、「編集(E)...」ボタンをクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image012.png') }}" alt="Path編集画面">
            <p class="caption">図2.11：Path編集画面</p>
        </div>
        <li>「新規(N)」ボタンをクリックし、HerdのPHPのパス</br>（例: C:\Users\ユーザー名\AppData\Local\Programs\Herd\php）を追加します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image013.png') }}" alt="Path追加画面">
            <p class="caption">図2.12：Path追加画面</p>
        </div>
        <li>すべてのウィンドウで「OK」をクリックして変更を保存します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image014.png') }}" alt="Path保存画面">
            <p class="caption">図2.13：Path保存画面</p>
        </div>
</section>
