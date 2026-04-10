<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>構築ガイドメニュー</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        .guide-card {
            background: white; padding: 20px; border-radius: 8px;
            margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex; justify-content: space-between; align-items: center;
            text-decoration: none; color: #333; transition: 0.2s;
        }
        .guide-card:hover { transform: translateX(10px); background: #e3f2fd; }
        .guide-card h3 { margin: 0; color: #1a73e8; }
        .nav-back { margin-bottom: 20px; display: inline-block; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ route('admin.menu') }}" class="nav-back">← 管理者メニューに戻る</a>
        <h1>部材・構築ガイド 目次</h1>
        <p>確認したい項目を選択してください。</p>

        <a href="{{ route('guidebook.install') }}" class="guide-card">
            <div>
                <h3>🛠️ Laravel & React インストール</h3>
                <p>環境構築と各フレームワークの導入手順について</p>
            </div>
            <span>➔</span>
        </a>

        <a href="{{ route('guidebook.design') }}" class="guide-card">
            <div>
                <h3>📐 基本設計・詳細設計</h3>
                <p>システム要件、DB設計、クラス構成のドキュメント</p>
            </div>
            <span>➔</span>
        </a>

        <a href="{{ route('guidebook.construction') }}" class="guide-card">
            <div>
                <h3>💻 業務管理システムの構築</h3>
                <p>勤怠・経費・社員管理の具体的な実装フロー</p>
            </div>
            <span>➔</span>
        </a>
    </div>
</body>
</html>