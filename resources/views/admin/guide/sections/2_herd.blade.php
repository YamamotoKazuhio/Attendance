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
            <img src="{{ asset('images/guide/install/image001.png') }}" alt="images/guide/install/image001.png">
        </div>
        <li>Herdのダウンロードが開始されます。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image002.png') }}" alt="images/guide/install/image002.png">
        </div>
        <li>ダウンロードフォルダに[Herd-1.25.0-setup.exe]ファイルが生成されるので、これをクリックします。</li>
        <div>
            <img src="{{ asset('images/guide/install/image003.png') }}" alt="images/guide/install/image003.png">
        </div>
        <li>[Herd]のインストールが開始されます。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image004.png') }}" alt="images/guide/install/image004.png">
        </div>
        <li>以下の表示になれば、インストールは完了です。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image005.png') }}" alt="images/guide/install/image005.png">
        </div>
        <li>Windowsメニュー[タスクメニュー]よりHerdのアイコンをクリックします。</li>
        <div>
            <img src="{{ asset('images/guide/install/image006.png') }}" alt="images/guide/install/image006.png">
        </div>
        <li>以下のような画面が表示されることを確認します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image007.png') }}" alt="images/guide/install/image007.png">
        </div>
        <li>インストールが完了すると、自動的に PHP と Composer がPCで使えるようになります。</br>
            正しく入ったか、コマンドプロンプトを開いて以下のコマンドを打ってみてください。</br>
            <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
                php -v</br>
                composer -V
            </p>
          </li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image008.png') }}" alt="images/guide/install/image008.png">
        </div>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image009.png') }}" alt="images/guide/install/image009.png">
        </div>
    </ol>

    <h3 id="herd-config">2-2. パス（Path）を登録する手順</h3>
    <p>以下の手順で、HerdのPHPをPCのメインに設定します。</p>
    <ol class="guide-steps">
        <li>Windowsのスタートメニューから「環境変数」と検索し、「システム環境変数を編集」を選択します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image010.png') }}" alt="images/guide/install/image010.png">
        </div>
        <li>「システムのプロパティ」ウィンドウが開いたら、下部の「環境変数(N)...」ボタンをクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image011.png') }}" alt="images/guide/install/image011.png">
        </div>
        <li>「システム環境変数」のリストから「Path」を選択し、「編集(E)...」ボタンをクリックします。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image012.png') }}" alt="mages/guide/install/image012.png">
        </div>
        <li>「新規(N)」ボタンをクリックし、HerdのPHPのパス</br>（例: C:\Users\ユーザー名\AppData\Local\Programs\Herd\php）を追加します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image013.png') }}" alt="images/guide/install/image013.png">
        </div>
        <li>すべてのウィンドウで「OK」をクリックして変更を保存します。</li>
        <div class="guide-figure">
            <img src="{{ asset('images/guide/install/image014.png') }}" alt="images/guide/install/image014.png">
        </div>
</section>
