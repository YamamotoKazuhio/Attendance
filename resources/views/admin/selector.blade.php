<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者メニュー 勤怠・経費管理</title>
    <style>
        .menu-container { display: flex; flex-wrap: wrap; gap: 20px; padding: 20px; justify-content: center; }
        .menu-card { 
            width: 250px; padding: 30px; border: 1px solid #ddd; border-radius: 8px; 
            text-align: center; text-decoration: none; color: #333; transition: 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .menu-card:hover { transform: translateY(-5px); box-shadow: 0 6px 12px rgba(0,0,0,0.15); border-color: #ff9800; }
        .icon { font-size: 40px; margin-bottom: 15px; display: block; }
        .title { font-size: 18px; font-weight: bold; }
        .desc { font-size: 14px; color: #666; margin-top: 10px; }
        /* 申請承認カードの強調用 */
        .highlight { border-top: 5px solid #ff9800; }
        
        /* ★ マニュアルカード用のスタイル（少し色を変える場合など） */
        .manual-card { border-top: 5px solid #6c757d; }
    </style>
</head>
<body>
    @php
        $user = Auth::user();
        $isAdmin = $user && $user->is_admin == 1;
        $role = optional($user->detail)->role;
        // is_admin = 1 なら全表示、それ以外で Class_B または Class_K の場合は非表示にする
        $showRestrictedMenu = $isAdmin || !in_array($role, ['Class_B', 'Class_K']);
    @endphp

    <div style="padding: 20px 20px 0 20px;">
        <a href="http://portalsight.test/dashboard" class="portal-back">
            ← ポータルサイトへ戻る
        </a>
    </div>
    <h1 style="text-align: center; margin-top: 40px;">勤怠・経費管理システム 管理者メニュー</h1>

    <div class="menu-container">
        {{-- 社員名簿管理 (Class_B, Class_K は非表示) --}}
        @if($showRestrictedMenu)
        <a href="{{ route('admin.users.index') }}" class="menu-card">
            <span class="icon">👥</span>
            <span class="title">社員名簿管理</span>
            <p class="desc">全社員の基本情報・連絡先の確認および編集を行います。</p>
        </a>
        @endif

        <a href="{{ route('admin.monthly_report') }}" class="menu-card">
            <span class="icon">📊</span>
            <span class="title">月次集計・確定</span>
            <p class="desc">全社員の稼働時間確認と、月次データの確定処理を行います。</p>
        </a>

        <a href="{{ route('admin.leave_requests') }}" class="menu-card highlight">
            <span class="icon">📩</span>
            <span class="title">休暇・残業申請承認</span>
            <p class="desc">社員から届いた有給や残業の事前申請を確認・承認します。</p>
        </a>

        <a href="{{ route('expenses.admin') }}" class="menu-card">
            <span class="icon">💰</span>
            <span class="title">経費精算管理</span>
            <p class="desc">提出された経費精算書の確認と承認・却下を行います。</p>
        </a>

        @if($isAdmin)
        <a href="{{ route('payment.admin') }}" class="menu-card">
            <span class="icon">💳</span>
            <span class="title">支払管理</span>
            <p class="desc">支払処理の確認と管理を行います。</p>
        </a>
        @endif
        
        <a href="{{ route('attendance.index') }}" class="menu-card">
            <span class="icon">📅</span>
            <span class="title">勤務表画面</span>
            <p class="desc">自身の勤務表、または特定社員の勤務表入力画面に戻ります。</p>
        </a>

        {{-- 操作ガイド・構築ガイド（Class_B, Class_K は非表示） --}}
        @if($showRestrictedMenu)
        <a href="{{ url('/manual') }}" class="menu-card manual-card">
            <span class="icon">📖</span>
            <span class="title">操作ガイド</span>
            <p class="desc">システムの操作図解（PDF）を表示します。別タブで開きます。</p>
        </a>

        <a href="{{ route('guidebook.menu') }}" class="menu-card">
            <span class="icon">📖</span>
            <span class="title">構築ガイド（目次）</span>
            <p class="desc">設計書・インストール手順・実装フローの確認</p>
        </a>
        @endif
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="background: none; border: none; color: blue; text-decoration: underline; cursor: pointer;">サインアウト</button>
        </form>
    </div>
</body>
</html>