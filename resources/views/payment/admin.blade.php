<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>支払管理メニュー 勤怠・経費管理</title>
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
        .back-link { padding: 20px 20px 0 20px; }
        .back-link a { text-decoration: none; color: #007bff; font-weight: bold; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    @php
        $user = Auth::user();
        $isAdmin = $user && $user->is_admin == 1;
        $role = optional($user->detail)->role;
        $department = optional($user->detail)->department;

        $isSoumuClassNA = (!$isAdmin && $department === '総務部' && strcasecmp($role, 'Class_NA') === 0);
    @endphp

    <div class="back-link">
        @if($isSoumuClassNA)
            <a href="{{ route('soumu.menu') }}">← 総務部メニューへ戻る</a>
        @else
            <a href="{{ route('admin.menu') }}">← 管理メニューへ戻る</a>
        @endif
    </div>
    
    <h1 style="text-align: center; margin-top: 40px;">支払管理メニュー</h1>

    <div class="menu-container">
        {{-- 給与管理ボタン --}}
        <a href="{{ route('payment.payroll') }}" class="menu-card">
            <span class="icon">💼</span>
            <span class="title">給与支払管理</span>
            <p class="desc">給与計算や支給に関する<br />データ管理を行います。</p>
        </a>

        {{-- 経費支払管理ボタン --}}
        <a href="{{ route('payment.expenses.index') }}" class="menu-card" style="border-top: 5px solid #1a73e8;">
            <span class="icon">🧾</span>
            <span class="title">経費支払管理</span>
            <p class="desc">承認された経費データの確認や支払に向けた集計を行います。</p>
        </a>
    </div>
    <div style="text-align: center; margin-top: 30px;">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="background: none; border: none; color: blue; text-decoration: underline; cursor: pointer;">サインアウト</button>
        </form>
    </div>
</body>
</html>