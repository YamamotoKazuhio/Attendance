<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laravel & React インストール手順</title>
</head>
<style>
    .guide-wrapper {
        display: flex;
        min-height: 100vh;
    }

    /* 左側目次 */
    .sidebar {
        width: 250px;
        background: #2c3e50;
        color: white;
        padding: 20px;
        position: sticky;
        top: 0;
        height: 100vh;
    }

    .sidebar a {
        display: block;
        color: #bdc3c7;
        text-decoration: none;
        margin-bottom: 15px;
        font-size: 14px;
    }

    .sidebar a.active {
        color: #fff;
        font-weight: bold;
    }

    /* 子メニューは最初非表示 */
    .submenu {
        display: none;
        padding-left: 15px;
        /* 子項目の字下げ */
        margin-bottom: 10px;
    }

    /* openクラスがついたら表示 */
    .submenu.open {
        display: block;
    }

    .submenu a {
        font-size: 13px !important;
        color: #95a5a6 !important;
        margin-bottom: 8px !important;
    }

    html {
        scroll-behavior: smooth;
    }

    /* 子メニューがアクティブな時の色 */
    .submenu a.active {
        color: #fff !important;
    }

    /* 中央コンテンツ */
    .content-area {
        flex: 1;
        padding: 40px;
        background: #fff;
        line-height: 1.8;
    }

    .doc-content {
        max-width: 800px;
        margin: 0 auto;
    }

    /* ページめくりボタン */
    .pager {
        display: flex;
        justify-content: space-between;
        margin-top: 50px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .btn-page {
        padding: 10px 20px;
        border: 1px solid #1a73e8;
        color: #1a73e8;
        text-decoration: none;
        border-radius: 5px;
        transition: 0.3s;
    }

    .btn-page:hover {
        background: #1a73e8;
        color: white;
    }

</style>
<body>
    <div class="guide-wrapper">
        <nav class="sidebar">
            <h3>目次</h3>

            <div class="menu-item">
                <a href="#env" class="parent-link">1. LaravelとReactについて</a>
                <div class="submenu" id="submenu-env">
                    <a href="#env-laravel">1-1. Laravel（ララベル）とは？</a>
                    <a href="#env-react">1-2. React（リアクト）とは？</a>
                    <a href="#env-relation">1-3. Laravel と React の関係</a>
                </div>
            </div>

            <div class="menu-item">
                <a href="#herd" class="parent-link">2. Laravel Herd のインストール</a>
                <div class="submenu" id="submenu-herd">
                    <a href="#herd-setup">2-1. セットアップ</a>
                    <a href="#herd-config">2-2. パス（Path）を登録する手順</a>
                </div>
            </div>

            <div class="menu-item">
                <a href="#laravel" class="parent-link">3. Laravelのプロジェクト作成</a>
                <div class="submenu" id="submenu-laravel">
                    <a href="#laravel-create">3-1. プロジェクトの作成</a>
                </div>
            </div>

            <div class="menu-item">
                <a href="#mysql" class="parent-link">4. データベース（MySQL）の起動</a>
                <div class="submenu" id="submenu-mysql">
                    <a href="#mysql-download">4-1. MySQLのダウンロード</a>
                    <a href="#mysql-install">4-2. MySQLのインストール</a>
                    <a href="#mysql-config">4-3. .envファイルを書き換える</a>
                    <a href="#mysql-env">4-4. 環境変数の設定</a>
                    <a href="#mysql-create">4-5. データベースの作成</a>
                </div>
            </div>

            <div class="menu-item">
                <a href="#react" class="parent-link">5. Reactの導入</a>
                <div class="submenu" id="submenu-react">
                    <a href="#react-intro">5-1. Reactの導入</a>
                    <a href="#react-prepare">5-2. 事前準備</a>
                    <a href="#react-vite">5-3. Viteでの導入</a>
                    <a href="#react-other">5-4. その他の方法</a>
                </div>
            </div>

            <hr>
            <a href="{{ route('guidebook.menu') }}" style="color:orange;">🔙 目次に戻る</a>
        </nav>

        <main class="content-area">
            <div class="doc-content">
                <h1>🛠️ Laravel & React 構築手順</h1>

                @include('admin.guide.sections.env') {{-- 1. LaravelとReactについて --}}
                @include('admin.guide.sections.herd') {{-- 2. Laravel Herd のインストール --}}
                @include('admin.guide.sections.laravel') {{-- 3. Laravelのプロジェクト作成 --}}
                @include('admin.guide.sections.mysql') {{-- 4. MySQL --}}
                @include('admin.guide.sections.react') {{-- 5. React導入 --}}

                <div class="pager">
                    <span></span> {{-- 最初のページなので「前へ」は空 --}}
                    <a href="{{ route('guidebook.design') }}" class="btn-page">次へ：設計書について →</a>
                </div>
            </div>
        </main>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const menuLinks = document.querySelectorAll('.sidebar a');
                const allSubmenus = document.querySelectorAll('.submenu');
                const sections = document.querySelectorAll('section[id], h3[id]');

                // 1. クリックイベント
                menuLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        menuLinks.forEach(l => l.classList.remove('active'));
                        this.classList.add('active');

                        const href = this.getAttribute('href');
                        const targetSubmenu = this.parentElement.querySelector('.submenu');

                        // 親リンク（IDにハイフンが含まれないもの）をクリックした場合のみ開閉
                        if (!href.includes('-')) {
                            allSubmenus.forEach(sub => {
                                if (sub !== targetSubmenu) sub.classList.remove('open');
                            });
                            if (targetSubmenu) {
                                targetSubmenu.classList.toggle('open');
                            }
                        }
                    });
                });

                // 2. スクロール監視
                window.addEventListener('scroll', () => {
                    let current = "";
                    sections.forEach(section => {
                        if (window.pageYOffset >= (section.offsetTop - 150)) {
                            current = section.getAttribute('id');
                        }
                    });

                    if (current) {
                        menuLinks.forEach(link => {
                            if (link.getAttribute('href') === `#${current}`) {
                                menuLinks.forEach(l => l.classList.remove('active'));
                                link.classList.add('active');

                                // スクロール先の親メニューを自動で開く
                                const parentMenuItem = link.closest('.menu-item');
                                const parentSubmenu = parentMenuItem ? parentMenuItem.querySelector('.submenu') : null;

                                if (parentSubmenu) {
                                    // 他を閉じて現在地を開く
                                    allSubmenus.forEach(sub => {
                                        if (sub !== parentSubmenu) sub.classList.remove('open');
                                    });
                                    parentSubmenu.classList.add('open');
                                }
                            }
                        });
                    }
                });
            });

        </script>
    </div>
</body>
</html>
