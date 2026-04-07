<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>勤務表 - {{ $targetUser->detail->name_kanji ?? $targetUser->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/Attendance.css') }}">
    <style>
        /* 背景画像のパスを / 始まりに修正（404対策） */
        body { background-image: url("{{ asset('/png/favicon_1.png') }}"); }
        .sunday, .holiday { background-color: #ffebee !important; }
        .saturday { background-color: #e3f2fd !important; }
        .logout-button { background: none; border: none; color: blue; text-decoration: underline; cursor: pointer; padding: 0; }
        
        .header-container { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background: rgba(255,255,255,0.8); border-bottom: 1px solid #ddd; }
        .header-left { display: flex; gap: 10px; align-items: center; }
        .month-nav-container { display: flex; justify-content: center; align-items: center; gap: 20px; margin: 20px 0; }
        .month-nav-btn { padding: 8px 16px; background-color: #1976d2; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        
        .btn-header { display: inline-block; padding: 8px 15px; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 0.9em; }
        .btn-profile { background-color: #4caf50; }
        .btn-expense { background-color: #007bff; }
        .btn-guide { background-color: #9c27b0; }
        .btn-leave { background-color: #ff9800; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold; }

        #emp { margin-left: 20px; background: white; padding: 15px; border-radius: 8px; display: inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.1); min-width: 950px; }
        .btn-confirm-basic { background-color: #17a2b8; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; margin-left: 10px; vertical-align: middle; }

        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fff; margin: 10% auto; padding: 20px; border-radius: 8px; width: 450px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        
        /* 有給残数の強調表示 */
        .paid-remaining-val { font-size: 1.2em; color: #d32f2f; font-weight: bold; }
    </style>
</head>

<body>
    @php
        // データの安全な初期化
        $safeAttendances = $attendances ?? [];
        $safeHolidays = $holidays ?? [];
        $safeTargetMonth = $targetMonth ?? date('Y-m');
        
        // 申請中・承認済みの休暇日付を取得（JS側での制御用）
        $existingApps = [];
        try {
            $existingApps = \DB::table('leave_applications')
                ->where('user_id', $targetUser->id)
                ->whereIn('status', [0, 1])
                ->pluck('target_date')
                ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
                ->toArray();
        } catch (\Exception $e) {
            Log::error("Leave App Fetch Error: " . $e->getMessage());
        }
    @endphp

    <div class="header-container">
        <div class="header-left">
            <a href="{{ route('user_details.show', Auth::id()) }}" class="btn-header btn-profile">👤 プロフィール</a>
            <a href="{{ route('expenses.index') }}" class="btn-header btn-expense">💰 経費精算</a>
            <a href="{{ route('guide.show') }}" class="btn-header btn-guide" style="margin-left: 5px;">📖 操作ガイド</a>
        </div>
        <div>
            @if(Auth::user()->is_admin)
                <a href="{{ route('admin.menu') }}" style="margin-right: 15px; font-weight: bold; color: #1976d2; text-decoration: none;">⚙ 管理メニュー</a>
            @endif
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-button">サインアウト</button>
            </form>
        </div>
    </div>

    <div class="month-nav-container">
        <button id="btnPrevMonth" class="month-nav-btn">◀ 前月</button>
        <h2 id="title" style="margin: 0; min-width: 200px; text-align: center;">{{ substr($safeTargetMonth, 0, 4) }}年{{ substr($safeTargetMonth, 5, 2) }}月 勤務表</h2>
        <button id="btnNextMonth" class="month-nav-btn">翌月 ▶</button>
    </div>

    <div id="emp">
        <div id="user_info">
            {{-- 社員番号 --}}
            社員番号：<input type="text" value="{{ $targetUser->detail->employee_code ?? '未設定' }}" disabled style="width:100px; background-color: #eee;">
            
            {{-- 氏名（漢字名優先） --}}
            氏名：<input type="text" value="{{ $targetUser->detail->name_kanji ?? $targetUser->name }}" disabled style="width:160px; background-color: #eee; font-weight: bold;">
            
            {{-- 所属（Userモデル優先） --}}
            所属：<select id="empDept">
                    <option value="">未設定</option>
                    @foreach(['総務グループ', '営業グループ', '第１グループ', '第２グループ'] as $dept)
                        <option value="{{ $dept }}" {{ (($targetUser->department ?? ($targetUser->detail->department ?? '')) == $dept) ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>

            {{-- 通勤費（日額） --}}
            通勤費：<input type="number" id="commuteCost" value="{{ $targetUser->daily_commute_fee ?? 0 }}" oninput="updateLiveCommuteTotal()" style="width:70px;"> 円
            <button type="button" class="btn-confirm-basic" onclick="submitBasicInfo()">確定</button>
            {{--ステータス表示 --}}
            <span style="margin-left:20px; font-weight: bold;">ステータス：</span>
            @php
                $totalDays = $attendances->count();
                // 状態ごとのカウント（is_lockedのカラム値に準拠）
                $approvedCount = $attendances->where('is_locked', 2)->count(); // 承認済み
                $pendingCount = $attendances->where('is_locked', 1)->count();  // 承認待ち（確定済み）
                $unregisteredCount = $attendances->where('is_locked', 0)->count(); // 未登録（または登録ボタン状態）
            @endphp

            {{-- id="monthlyStatusDisplay" を付与し、初期表示のスタイルを動的に設定 --}}
            <span id="monthlyStatusDisplay" style="
                padding: 3px 10px; 
                border-radius: 4px; 
                border: 1px solid; 
                font-weight: bold;
                @if($approvedCount === $totalDays && $totalDays > 0)
                    color: #2e7d32; background: #e6ffed; border-color: #2e7d32;
                @elseif($approvedCount > 0)
                    color: #f57c00; background: #fff3e0; border-color: #f57c00;
                @elseif($pendingCount > 0)
                    color: #d32f2f; background: #fff5f5; border-color: #d32f2f;
                @else
                    color: #666; background: #eee; border-color: #ccc;
                @endif
            ">
                @if($approvedCount === $totalDays && $totalDays > 0)
                    ✅ 承認済み（修正不可）
                @elseif($approvedCount > 0)
                    ⚠️ 承認済み（一部のみ）
                @elseif($pendingCount > 0)
                    🚩 確定（承認待ち）
                @else
                    未登録
                @endif
            </span>

            {{-- 有給残数 --}}
            <span style="margin-left:25px;">有給残：</span>
            <span class="paid-remaining-val">
                {{ number_format($targetUser->paid_leave_remaining ?? 0, 1) }}
            </span> 日
        </div>
    </div>
    {{-- 既存のコードのどこか（formの中など）に追加 --}}
    <input type="hidden" id="target_user_id" value="{{ $targetUser->id }}">

    <table id="workTable" style="margin: 20px auto; width: 95%;">
        <thead>
            <tr><th>登録</th><th>日付</th><th>特殊</th><th>勤怠</th><th>勤務先</th><th>シフト</th><th>開始</th><th>終了</th><th>休憩</th><th>残業</th><th>稼働</th></tr>
        </thead>
        <tbody id="tableBody">
            {{-- JS(Attendance.js)によって動的に生成されます --}}
        </tbody>
    </table>

    <div class="attendance-totals" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 50px;">
        <div style="display: flex; gap: 40px; font-weight: bold; font-size: 1.1em; justify-content: center;">
            <div>稼働時間合計: <span id="totalWorkTime" style="color: #007bff;">0.00</span> 時間</div>
            <div>残業時間合計: <span id="totalOvertime" style="color: #d32f2f;">0.00</span> 時間</div>
            <div>通勤費合計: <span id="totalCommuteCost" style="color: #2e7d32;">0</span> 円</div>
        </div>
    </div>

    <<script>
        // グローバル変数の設定（外部JSファイルで使用）
        window.displayUserId = {{ $targetUser->id }};
        window.savedDataFromServer = @json($safeAttendances);
        window.isAdmin = {{ Auth::user()->is_admin ? 'true' : 'false' }};
        window.japaneseHolidays = @json($safeHolidays);
        window.currentMonthStr = "{{ $safeTargetMonth }}";
        window.existingApplications = @json($existingApps);

        // 基本情報（所属・通勤費）の非同期保存
        function submitBasicInfo() {
            const dept = document.getElementById('empDept').value;
            const fee = document.getElementById('commuteCost').value;
            if(!confirm('所属と通勤費を保存しますか？')) return;

            fetch("{{ route('attendance.updateBasicInfo') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Accept": "application/json"
                },
                body: JSON.stringify({ user_id: window.displayUserId, department: dept, commute_fee: fee })
            })
            .then(res => res.json())
            .then(data => { 
                alert(data.message); 
                location.reload(); 
            })
            .catch(err => alert("通信エラーが発生しました。"));
        }

        // 通勤費のリアルタイム計算（出勤日数 × 日額）
        function updateLiveCommuteTotal() {
            const dailyFee = parseFloat(document.getElementById('commuteCost').value) || 0;
            let workDays = 0;
            const rows = document.querySelectorAll('#tableBody tr');
            rows.forEach(row => {
                const select = row.querySelector('select');
                let typeText = select ? select.value : (row.cells[3] ? row.cells[3].innerText.trim() : '');
                if (['出勤', '現場出勤', '通常勤務', '振替出勤'].includes(typeText)) {
                    workDays++;
                }
            });
            const display = document.getElementById('totalCommuteCost');
            if (display) display.innerText = (workDays * dailyFee).toLocaleString();
        }

        document.addEventListener('DOMContentLoaded', () => {
            // テーブル描画後に計算を走らせるため少し遅延
            setTimeout(updateLiveCommuteTotal, 800);
            
            // 勤怠種別が変わったら通勤費を再計算
            document.getElementById('tableBody').addEventListener('change', (e) => {
                if (e.target.classList.contains('attendance-type-select')) {
                    const select = e.target;
                    const row = select.closest('tr');
                    const type = select.value;

                    const shift = row.querySelector('.shift-select');
                    const startTime = row.querySelector('.start-time-input');
                    const endTime = row.querySelector('.end-time-input');
                    const breakTime = row.querySelector('.break-time-input');
                    const overtime = row.querySelector('.overtime-input');
                    const workTime = row.querySelector('.work-time-input');

                    if (type === '欠勤' || type === '有給') {
                        if (shift) shift.selectedIndex = 0;
                        if (startTime) startTime.value = '';
                        if (endTime) endTime.value = '';
                        if (breakTime) breakTime.value = '0.00';
                        if (overtime) overtime.value = '0.00';
                        if (workTime) {
                            workTime.value = (type === '欠勤') ? '-8.00' : '8.00';
                        }
                    }
                    updateLiveCommuteTotal(); // 再計算を呼ぶ
                }
            });
        }); // ← ここで閉じカッコが必要でした
    </script>

    <script src="{{ asset('js/Attendance.js') }}"></script>
</body>
</html>