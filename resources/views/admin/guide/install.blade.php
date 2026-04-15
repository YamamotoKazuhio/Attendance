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

    /* --- 画像の基本サイズ設定 --- */
    .guide-figure img {
        width: 300px;
        /* 小さく表示するサイズ（お好みで調整） */
        height: auto;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        /* マウスを乗せた時に指マークにする */
        transition: 0.3s;
    }

    .guide-figure img:hover {
        opacity: 0.8;
        transform: scale(1.02);
    }

    /* --- 拡大表示用モーダルのスタイル --- */
    #image-modal {
        display: none;
        /* 初期状態は非表示 */
        position: fixed;
        z-index: 9999;
        /* 最前面に表示 */
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.85);
        /* 背景を暗く */
        justify-content: center;
        align-items: center;
        cursor: zoom-out;
        /* クリックで閉じれることを示す */
    }

    #modal-img {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        animation: zoomIn 0.3s;
        /* ふわっと表示 */
    }

    @keyframes zoomIn {
        from {
            transform: scale(0.8);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

</style>

<div id="image-modal" onclick="this.style.display='none'">
    <img id="modal-img" src="" alt="拡大図">
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('image-modal');
        const modalImg = document.getElementById('modal-img');
        
        // .guide-figure クラス内のすべての画像をクリック対象にする
        const images = document.querySelectorAll('.guide-figure img');

        images.forEach(img => {
            img.addEventListener('click', () => {
                modalImg.src = img.src; // クリックした画像のURLをコピー
                modal.style.display = 'flex'; // 表示
            });
        });
    });
</script>

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
                <a href="#mysql" class="parent-link">4. Windows版データベース（MySQL）の起動</a>
                <div class="submenu" id="submenu-mysql">
                    <a href="#mysql-download">4-1. MySQLのダウンロード</a>
                    <a href="#mysql-install">4-2. MySQLのインストール</a>
                    <a href="#mysql-config">4-3. .envファイルを書き換える</a>
                    <a href="#mysql-env">4-4. 環境変数の設定</a>
                    <a href="#mysql-create">4-5. データベースの作成</a>
                </div>
            </div>

             <div class="menu-item">
                <a href="#mysql_linux" class="parent-link">5. Linux版データベース（MySQL）の起動</a>
                <div class="submenu" id="submenu-mysql_linux">
                    <a href="#mysql_linux-download">5-1. MySQLのダウンロードおよびインストール</a>
                    <a href="#mysql_linux-create">5-2. データベースの作成</a>
                </div>
             </div>

            <div class="menu-item">
                <a href="#react" class="parent-link">6. Reactの導入</a>
                <div class="submenu" id="submenu-react">
                    <a href="#react-intro">6-1. Reactの導入</a>
                    <a href="#react-prepare">6-2. 事前準備</a>
                    <a href="#react-vite">6-3. Viteでの導入</a>
                    <a href="#react-other">6-4. その他の方法</a>
                </div>
            </div>

            <div class="menu-item">
                <a href="#remarks" class="remarks-link">7. 備考</a>
            </div>

            <hr>
            <a href="{{ route('guidebook.menu') }}" style="color:orange;">🔙 目次に戻る</a>
        </nav>

        <main class="content-area">
            <div class="doc-content">
                <h1>🛠️ Laravel & React 構築手順</h1>

                @include('admin.guide.sections.1_env') {{-- 1. LaravelとReactについて --}}
                @include('admin.guide.sections.2_herd') {{-- 2. Laravel Herd のインストール --}}
                @include('admin.guide.sections.3_laravel') {{-- 3. Laravelのプロジェクト作成 --}}
                @include('admin.guide.sections.4_mysql_win') {{-- 4. MySQL_windows --}}
                @include('admin.guide.sections.5_mysql_Linux') {{-- 5. MySQL_Linux --}}
                @include('admin.guide.sections.6_react') {{-- 6. React導入 --}}
                @include('admin.guide.sections.7_remarks') {{-- 7. 備考 --}}

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
