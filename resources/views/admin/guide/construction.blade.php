<!DOCTYPE html>
<html lang="jp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>実装フロー</title>
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
        display: block !important;
        ;
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

    /* プレビュー：10行程度（約220px）で固定してスクロールさせる */
    .code-preview {
        max-height: 220px;
        overflow-y: auto;
        border-radius: 5px 5px 0 0;
        border: 1px solid #444;
        text-align: left;
    }

    /* ボタンのデザイン */
    .view-full-btn {
        width: 100%;
        background: #444;
        color: white;
        border: none;
        padding: 8px;
        cursor: pointer;
        border-radius: 0 0 5px 5px;
        font-size: 0.9rem;
        transition: 0.3s;
    }

    .view-full-btn:hover {
        background: #666;
    }

    /* モーダル（全画面）表示のスタイル */
    .code-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        padding: 20px;
    }

    .modal-content {
        background: #2d2d2d;
        height: 90vh;
        margin-top: 20px;
        overflow: auto;
        border-radius: 10px;
        position: relative;
    }

    .close-modal {
        position: fixed;
        top: 30px;
        right: 40px;
        background: #ff4444;
        color: white;
        border: none;
        padding: 8px 20px;
        cursor: pointer;
        border-radius: 5px;
        font-weight: bold;
        z-index: 10000;
    }

</style>

<div id="commonModal" class="code-modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; padding: 20px;">
    <button onclick="closeModal()" style="position: fixed; top: 20px; right: 40px; background: #ff4444; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; font-weight: bold; z-index: 10000;">× 閉じる</button>
    <div class="modal-content" style="background: #2d2d2d; height: 90vh; margin-top: 20px; overflow: auto; border-radius: 10px; padding: 20px;">
        <div id="modalBody"></div>
    </div>
</div>

<body>
    </div>
    <div class="guide-wrapper">
        <nav class="sidebar">
            <h3>目次</h3>

            <div class="menu-item">
                <a href="#requirements-definition" class="parent-link">1. データベースのテーブルを作成</a>
                <div class="submenu" id="submenu-basic">
                    <a href="#migration-file">1-1. 設計図（マイグレーションファイル）を作成する</a>
                    <a href="#run-migration">1-2. 設計図をデータベースに反映する</a>
                    <a href="#rollback">1-3. やり直し・状態確認に使うコマンド（重要）</a>
                    <a href="#time-saving">1-4. モデルと同時に作成する時短テクニック</a>
                    <a href="#caution">1-5. 【注意】AWSなど本番環境での実行</a>
                    <a href="#hints">1-6. 実行のヒント</a>
                </div>
            </div>

            <div class="menu-item">
                <a href="#logon" class="parent-link">2. Laravel Breeze（Starter Kit）でログイン機能を構築</a>
                <div class="submenu" id="submenu-advanced">
                    <a href="#install-breeze">2-1. Laravel Breeze（Starter Kit）の導入</a>
                    <a href="#caution-EC2">2-2. EC2環境で特に注意すべき点</a>
                    <a href="#tinker">2-3. php artisan tinkerの利用（管理者の作成）</a>
                </div>
            </div>

            <div class="menu-item">
                <a href="#top" class="parent-link">3. トップページの作成</a>
                <div class="submenu" id="submenu-advanced">
                    <a href="#controller">3-1. 司令塔 (Controller)</a>
                    <a href="#routes">3-2. URLの設定 (Routes)</a>
                    <a href="#view">3-3. トップページ画面 (View)</a>
                    <a href="#construction-roadmap">3-4. 構築のロードマップ</a>
                </div>
            </div>

            <div class="menu-item">
                <a href="#admin" class="parent-link">4. 管理者のみのページの作成</a>
                <div class="submenu" id="submenu-advanced">
                    <a href="#administrator-only-menu">4-1. 管理者専用メニュー</a>
                    <a href="#employee-roster-management">4-2. 社員名簿管理</a>
                    <a href="#monthly-aggregation">4-3. 月次集計・確定</a>
                    <a href="#Approval-of-leave/overtime-requests">4-4. 休暇・残業申請承認</a>
                    <a href="#Expense-reimbursement-approval">4-5. 経費精算承認</a>
                    <a href="#Operation-guide">4-6. 操作ガイド</a>
                    <a href="#Construction-Guide-with-Table-of-Contents)">4-7. 構築ガイド（目次付）</a>
                </div>
            </div>

            <div class="menu-item">
                <a href="#users" class="parent-link">5. 管理者および一般ユーザー用ページの作成</a>
                <div class="submenu" id="submenu-advanced">
                    <a href="#Profile">5-1. プロフィール（ユーザー詳細情報）</a>
                    <a href="#work-schedule">5-2. 勤務表</a>
                    <a href="#Expense-reimbursement-management">5-3. 経費精算管理</a>
                    <a href="#Operation-guide-work-schedule">5-4. 操作ガイド（勤務表）</a>
                    <a href="#Operation-guide-expense-reimbursement">5-5. 操作ガイド（経費精算）</a>
                </div>
            </div>
            <hr>
            <a href="{{ route('guidebook.menu') }}" style="color:orange;">🔙 目次に戻る</a>
        </nav>

        <main class="content-area">
            <div class="doc-content">
                <h1>💻 業務管理システムの構築・ロードマップ</h1>

                @include('admin.guide.road_map.1_tables'){{-- 1.データベースのテーブルを作成 --}}
                @include('admin.guide.road_map.2_logon'){{-- 2.Lavavel Breeze（Starter Kit）でログイン機能を構築 --}}
                @include('admin.guide.road_map.3_top'){{-- 3.トップページの作成 --}}
                @include('admin.guide.road_map.4_admin'){{-- 4.管理者のみのページの作成 --}}
                @include('admin.guide.road_map.5_users'){{-- 5.管理者および一般ユーザー用ページの作成 --}}
                <div class="pager">
                    <a href="{{ route('guidebook.design') }}" class="btn-page">← 前へ：設計書</a>
                </div>
            </div>
        </main>
    </div>
</body>

<script>
    // --- 1. スコープの外に定義する（これでどこからでも呼べるようになる） ---
    function openCodeModal(elementId) {
        const codeElement = document.getElementById(elementId);
        if (!codeElement) return;

        const modalBody = document.getElementById('modalBody');
        const commonModal = document.getElementById('commonModal');

        // 中身をコピー
        modalBody.innerHTML = codeElement.innerHTML;

        // 重要：コピー先のモーダル内では非表示設定(display:none)を解除して「ブロック要素」として表示する
        const hiddenContent = modalBody.querySelector('div, pre');
        modalBody.style.display = 'block';
        if (modalBody.firstChild) {
            modalBody.children[0].style.display = 'block';
        }

        commonModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('commonModal').style.display = 'none';
        document.body.style.overflow = 'auto'; // 背景スクロール再開
    }

    // --- 2. 既存のイベントリスナー（目次用など） ---
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
                    const parentLink = this.closest('.menu-item') ?.querySelector('.parent-link');
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
                        const parentSubmenu = parentMenuItem ?.querySelector('.submenu');
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
</html>
