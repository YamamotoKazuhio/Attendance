<section id="react">
    <h2 id="react-intro">6. Reactの導入</h2>
    <p>
        Reactをプロジェクトに導入する方法は、開発の目的（学習用、新規アプリ作成、既存サイトへの追加）によっていくつか種類があります。
        現在のモダンなフロントエンド開発で最も推奨される、Vite（ヴィート）を使った高速なインストール手順を中心に解説します。
    </p>

    <h3 id="react-prepare">6-1. 事前準備（Node.jsの確認）</h3>
    <ol class="guide-steps">
        <li>
            Reactを動かすには、JavaScriptの実行環境である Node.js が必要です。</br>
            ターミナル（コマンドプロンプト）で以下のコマンドを入力して、</br>
            インストール済みか確認してください。</br>
            <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
                node -v
            </p></br>
            <div class="guide-figure">
                <img src="{{ asset('images/guide/install/image057.png') }}" alt="Reactの動作確認画面1">
            </div>
        </li>

        <li>
            バージョン番号（例: v18.x.x 以上推奨）が表示されればOKです。</br>
            入っていない場合は、<a href="https://nodejs.org/" target="_blank">Node.js公式サイトから推奨版（LTS）</a>をインストールしてください。
        </li>
    </ol>

    <h3 id="react-vite">6-2. Vite を使った推奨インストール手順</h3>
    <p>Laravel 9.19以降では、Viteが公式にサポートされており、フロントエンドのビルドツールとして</br>推奨されています。</br>
       まずは、プロジェクトのルートディレクトリで以下のコマンドを実行して、ViteとReactの依存関係を</br>インストールします。
    </p>
    <ol class="guide-steps">
        <li>プロジェクトの作成</br>
            プロジェクトを作りたいディレクトリに移動し、以下のコマンドを実行します。
        </li>
        <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
            npm create vite@latest my-react-app
        </p>
        <p>上記コマンドは、Viteを使って「my-react-app」という名前のReactプロジェクトを作成します。</p>
        <li>テンプレートの選択<br>
             コマンドを打つと対話形式で質問されるので、矢印キーで選択します。</br>
             Select a framework: → React を選択</br>
             Select a variant: → JavaScript または TypeScript を選択
        </li>
        <li>ライブラリのインストールと起動</br>
            作成されたフォルダに移動し、必要なパッケージをインストールして実行します。
        </li>
        <p>
            <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
            cd my-react-app</br>
            npm install</br>
            npm run dev</br>
            </p>
            </br>
            実行後、ターミナルに表示されるURL（例: http://localhost:5173）をブラウザで開くと、</br>
            Reactの初期画面が表示されます。
        </p>
    </ol>

    <h3 id="react-other">6-3. その他のインストール方法</h3>
    <ol class="guide-steps">
        <li>A. Next.js（フルスタック開発向け）</li>
        <p>本格的なWebアプリ（SEO対策やサーバーサイドレンダリングが必要な場合）を作るなら、Reactベースのフレームワークである Next.js を使うのが一般的です。</p>
        <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
            npx create-next-app@latest my-next-app
        </p>
        <li>B. CDN（既存のHTMLに少しだけ使いたい場合）</li>
            <p>
                環境構築をせず、HTMLファイルに直接タグを貼って使う方法です。</br>
                HTML</br>
                <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
                    &lt;script src="https://unpkg.com/react@18/umd/react.development.js"&gt;&lt;/script&gt;</br>
                    &lt;script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"&gt;&lt;/script&gt;
                </p>
                </br>
                ただし、この方法は学習や小規模なプロジェクト向けで、実際の開発ではモジュールバンドラーを使うのが一般的です。
            </p>
        </li>
        <p>ただし、この方法は学習や小規模なプロジェクト向けで、実際の開発ではモジュールバンドラーを使うのが一般的です。</p>
    </ol>

    <h3>6-4. インストール後の構成</h3>
    <p>
        Viteでインストールすると、以下のようなファイル構成が作られます。</br>
        ・index.html: アプリの土台となるHTML。</br>
        ・src/main.jsx: Reactを起動するエントリーポイント。</br>
        ・src/App.jsx: 最初に表示されるコンポーネント（ここを書き換えて開発を始めます）。
    <p>

    




    
