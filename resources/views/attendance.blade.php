<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>勤務表 - {{ $targetUser->detail->name_kanji ?? $targetUser->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/Attendance.css') }}">
    @vite(['resources/js/Attendance.js'])
</head>
<body>
    <header class="header-container">
        <div class="header-left">
            <span>ログイン中: {{ Auth::user()->name }} さん</span>
            {{-- ポータルサイトへ戻るリンク --}}
            <a href="http://portalsight.test/dashboard" class="portal-back-link" style="margin-left: 15px; color: #2563eb; text-decoration: underline;">ポータルへ戻る</a>
            {{-- 管理者の場合のみ表示 --}}
            @if(Auth::user()->is_admin)
            <a href="{{ url('/admin/menu') }}" class="admin-menu-link" style="margin-left: 15px;">管理者メニューへ</a>
            @endif
        </div>
        <div class="header-center">
            <h2>社内ポータルシステム</h2>
        </div>
        <div class="header-right">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-button">ログアウト</button>
            </form>
        </div>
    </header>


    <div class="header-menu">
        <a href="{{ route('user_details.show', Auth::id()) }}">プロフィール詳細</a>
        <a href="{{ route('expenses.index') }}">経費精算</a> |
        <a href="{{ route('guide.show') }}">ガイドブック</a>
    </div>

    <main class="main-container">
        <div class="month-nav-container">
            <button type="button" class="month-nav-btn" onclick="changeMonth(-1)">前月</button>
            <h3 id="calendarTitle">{{ date('Y年m月', strtotime($targetMonth)) }}分 勤務表</h3>
            <button type="button" class="month-nav-btn" onclick="changeMonth(1)">次月</button>
            <button type="button" class="month-nav-btn" onclick="window.location.href='/attendance'" style="background:#28a745;">今月</button>
        </div>

        <div class="info-boxes">
            <div class="info-box" id="user_info">
                <strong>所属・基本情報</strong>
                <div class="info-wrapper" style="display: flex; gap: 40px; margin-top: 10px;">

                    <div class="info-left">
                        氏名: {{ $targetUser->detail->name_kanji ?? $targetUser->name }}<br>
                        社員コード: {{ $targetUser->detail->employee_code ?? '未設定' }}<br>
                        所属部署:
                        <select id="department" onchange="saveBasicInfo()">
                            <option value="">未設定</option>
                            @foreach(['開発部', '営業部', '総務部', '人事部', '経理部'] as $dept)
                            <option value="{{ $dept }}" {{ ($targetUser->detail->department ?? '') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="info-right">
                        <div class="info-row">
                            通勤費(一日当たり): <input type="number" id="commuteCost" value="{{ $targetUser->detail->commute_fee ?? 0 }}">
                            <button type="button" onclick="saveBasicInfo(); calculateTotals();">保存</button>
                            円/日分<br> (合計: <span id="totalCommuteDisplay">0 円</span>)
                        </div>
                        <div>
                            提出ステータス: <span id="statusLabel">読込中...</span>
                        </div>
                    </div>

                    <div id="paidLeaveDetails">
                        有給休暇状況<br>
                        累計付与: {{ $targetUser->getTotalPaidLeaveDays() }} 日<br>
                        累計消化: {{ $targetUser->getUsedPaidLeaveDays() }} 日<br>

                        <strong>現在日数: {{ $targetUser->paid_leave_remaining }} 日</strong>
                    </div>

                    <div id="monthlySummary">
                        当月集計<br>
                        <p>総労働時間: <span id="totalWorkTime">0.00</span> 時間</p>
                        <p>総残業時間: <span id="totalOvertime">0.00</span> 時間</p>
                    </div>
                </div>
            </div>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>承認状態</th>
                    <th>日付</th>
                    <th>特殊</th>
                    <th>勤務区分</th>
                    <th>就業場所</th>
                    <th>シフト</th>
                    <th>始業</th>
                    <th>終業</th>
                    <th>休憩(h)</th>
                    <th>残業(h)</th>
                    <th>労働(h)</th>
                </tr>
            </thead>
            <tbody id="tableBody">
            </tbody>
        </table>
    </main>

    <input type="hidden" id="user_id_field" name="user_id" value="{{ $targetUser->id }}">

    @vite(['resources/js/Attendance.js'])

    <script>
        // 管理者かどうかをJSに渡す
        window.isAdmin = {{Auth::user()->is_admin ? 'true' : 'false' }};

        // サーバーから渡されたデータをセット
        window.savedDataFromServer = @json($attendances);
        window.currentMonthStr = "{{ $targetMonth }}";

        // 読み込み完了後にデータを反映
        window.addEventListener('DOMContentLoaded', () => {
            // ここでカレンダー生成とステータス更新を確実に行う
            if (typeof window.loadData === 'function') {
                window.loadData();
            }
            if (typeof window.calculateTotals === 'function') {
                window.calculateTotals();
            }
        });

    </script>
</body>
</html>
