<!DOCTYPE html>
<html lang="jp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>基本設計・詳細設計書</title>
</head>
<style>
    .guide-wrapper {
        display: flex;
        min-height: 100vh;
    }

    /* 表全体のスタイル */
    table {
        width: 100%;
        border-collapse: collapse;
        /* 隣り合う線を一本にまとめる */
        margin-bottom: 20px;
    }

    /* 見出し(th)とセル(td)のスタイル */
    table th,
    table td {
        border: 1px solid #ccc;
        /* 線の太さ、種類、色 */
        padding: 10px;
        /* セル内の余白 */
        text-align: left;
        /* 文字の左寄せ */
    }

    /* 見出しの色を少し変えるとおしゃれになります */
    table th {
        background-color: #f8f9fa;
        font-weight: bold;
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

    .submenu {
        display: none;
        padding-left: 15px;
        margin-bottom: 10px;
    }

    .submenu.open {
        display: block !important;;
    }

    .submenu a {
        font-size: 13px !important;
        color: #95a5a6 !important;
        margin-bottom: 8px !important;
    }

    .submenu a.active {
        color: #fff !important;
    }

    html {
        scroll-behavior: smooth;
    }

</style>
<body>
    <div class="guide-wrapper">
        <nav class="sidebar">
            <h3>目次</h3>

            <div class="menu-item">
                <a href="#requirements-definition" class="parent-link">1. 要件定義</a>
                <div class="submenu" id="submenu-basic">
                    <a href="#project-overview">1-1. プロジェクトの概要</a>
                    <a href="#purpose-of-systemization">1-2. システム化の目的</a>
                    <a href="#business-requirements">1-3.業務要件（機能要件）</a>
                    <a href="#system-configuration">1-4.システム構成・技術要件</a>
                    <a href="#security-requirements">1-5. 権限・セキュリティ要件</a>
                    <a href="#non-functional-requirements">1-6. 非機能要件</a>
                    <a href="#function-list">1-7. 画面一覧（UI/UX要件）</a>
                </div>
            </div>

            <div class="menu-item">
                <a href="#basic-design" class="parent-link active">2. 基本設計</a>
                <div class="submenu" id="submenu-basic">
                    <a href="#ui-design">2-1. システム概要</a>
                    <a href="#function-list">2-2. 機能一覧</a>
                </div>
            </div>

            <div class="menu-item">
                <a href="#detail" class="parent-link">3. データベース設計（主要テーブル）</a>
            </div>

            <div class="menu-item">
                <a href="#permission-design" class="parent-link">4. 画面遷移・権限設計</a>
                <div class="submenu" id="submenu-detail">
                    <a href="#general-user-privileges">4-1. 一般ユーザー権限</a>
                    <a href="#administrator-privileges">4-2. 管理者権限(can:admin)</a>
                </div>
            </div>

            <div class="menu-item">
                <a href="#security-design" class="parent-link">5. セキュリティ設計</a>
            </div>

            <div class="menu-item">
                <a href="#Detailed-design-document" class="parent-link">6. 詳細設計</a>
                <div class="submenu" id="submenu-detail">
                    <a href="#database-physical-design">6-1. データベース物理設計</a>
                    <a href="#Application-logic-design">6-2. アプリケーションロジック設計</a>
                    <a href="#API-and-Routing-Design">6-3. API・ルーティング設計 (主要エンドポイント)</a>
                    <a href="#UI-UX-Specifications">6-4. UI/UX 詳細仕様</a>
                    <a href="#Infrastructure-deployment-configuration">6-5. インフラ・デプロイ構成</a>
                </div>
            </div>

            <div class="menu-item">
                <a href="#implementation-flow" class="parent-link">7. 実装フロー</a>
                <div class="submenu" id="submenu-flow">
                    <a href="#Overall-process-flow">7-1. 全体プロセスフロー</a>
                    <a href="#Detailed-sequence">7-2. 詳細シーケンス（勤怠・経費）</a>
                    <a href="#Key-checkpoints-for-installation">7-3. 実装上の重要チェックポイント（終了条件）</a>
                    <a href="#File-structure">7-4. ファイル構成（再確認）</a>
                </div>
            </div>
            <hr>
            <a href="{{ route('guidebook.menu') }}" style="color:orange;">🔙 目次に戻る</a>
        </nav>

        <main class="content-area">
            <div class="doc-content">
                <h1>📐 基本設計・詳細設計書</h1>
                @include('admin.guide.design.1_requirements'){{-- 要件定義 --}}
                @include('admin.guide.design.2_basic'){{-- 基本設計書 --}}
                @include('admin.guide.design.3_detail'){{-- 詳細設計書 --}}
                @include('admin.guide.design.4_flow'){{-- 実装フロー --}}
                <div class="pager">
                    <a href="{{ route('guidebook.install') }}" class="btn-page">← 前へ：インストール</a>
                    <a href="{{ route('guidebook.construction') }}" class="btn-page">次へ：実装フロー →</a>
                </div>
            </div>
        </main>
        </nav>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', () => {
    const menuLinks = document.querySelectorAll('.sidebar a');
    const allSubmenus = document.querySelectorAll('.submenu');
    const sections = document.querySelectorAll('section[id], h1[id], h2[id], h3[id]');

    // 1. クリックイベント
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // 全てのリンクから一旦 active を除去
            menuLinks.forEach(l => l.classList.remove('active'));
            // クリックしたリンクを白字・太字にする
            this.classList.add('active');

            // クリックされたリンクのすぐ次にある .submenu を探す
            const targetSubmenu = this.nextElementSibling;

            // もしクリックしたのが「親リンク（次にsubmenuがある）」なら
            if (targetSubmenu && targetSubmenu.classList.contains('submenu')) {
                // 他のサブメニューをすべて閉じる
                allSubmenus.forEach(sub => {
                    if (sub !== targetSubmenu) sub.classList.remove('open');
                });
                // 自分のサブメニューを開閉する
                targetSubmenu.classList.toggle('open');
            } else {
                // もしクリックしたのが「子リンク（submenuの中にある）」なら
                // 親のタイトル（parent-link）も active にする
                const parentLink = this.closest('.menu-item')?.querySelector('.parent-link');
                if (parentLink) parentLink.classList.add('active');
            }
        });
    });

    // 2. スクロール監視
    window.addEventListener('scroll', () => {
        let current = "";
        sections.forEach(section => {
            if (window.pageYOffset >= (section.offsetTop - 160)) {
                current = section.getAttribute('id');
            }
        });

        if (current) {
            menuLinks.forEach(link => {
                if (link.getAttribute('href') === `#${current}`) {
                    menuLinks.forEach(l => l.classList.remove('active'));
                    link.classList.add('active');

                    const parentMenuItem = link.closest('.menu-item');
                    const parentSubmenu = parentMenuItem?.querySelector('.submenu');
                    if (parentSubmenu) {
                        parentSubmenu.classList.add('open');
                        const parentLink = parentMenuItem.querySelector('.parent-link');
                        if (parentLink) parentLink.classList.add('active');
                    }
                }
            });
        }
    });
});
</script>
</body>
</html>
