<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>月次稼働集計</title>
    <style>
        body { font-family: sans-serif; background-color: #f8f9fa; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        .month-selector { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #007bff; color: white; white-space: nowrap; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .btn-finalize { background-color: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        .btn-edit { background-color: #17a2b8; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85em; display: inline-block; margin-bottom: 5px; }
        .status-finalized { color: #28a745; font-weight: bold; }
        .status-pending { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>月次稼働集計 ({{ $targetMonth }})</h2>

        <div class="month-selector">
            <form action="{{ route('admin.monthly-report') }}" method="GET">
                <input type="month" name="month" value="{{ $targetMonth }}" onchange="this.form.submit()">
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>社員番号</th>
                    <th>氏名</th>
                    <th>所属</th>
                    <th>稼働日数</th>
                    <th>今月の有給</th>
                    <th>累計消化</th>
                    <th>有給残日数</th>
                    <th>総稼働</th>
                    <th>残業</th>
                    <th>通勤費</th>
                    <th>ステータス</th>
                    <th>アクション</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $user)
                <tr>
                    {{-- ★ 社員番号 --}}
                    <td>{{ $user['employee_code'] }}</td>
                    
                    {{-- ★ 氏名（漢字氏名を優先） --}}
                    <td style="text-align: left; padding-left: 15px;">
                        <strong>{{ $user['name_kanji'] ?: $user['name'] }}</strong>
                    </td>

                    {{-- ★ 所属 --}}
                    <td>{{ $user['department'] ?: '-' }}</td>

                    <td>{{ $user['work_days'] }}日</td>
                    <td>{{ $user['paid_days'] }}日</td>
                    
                    {{-- 累計消化 --}}
                    <td>{{ $user['total_used_paid'] }}日</td>
                    
                    {{-- 有給残数 --}}
                    <td>{{ number_format($user['remaining_paid'], 1) }}日</td>

                    <td>{{ number_format($user['total_work'], 2) }}h</td>
                    <td>{{ number_format($user['total_ot'], 2) }}h</td>
                    
                    {{-- ★ 通勤費 --}}
                    <td style="text-align: right;">{{ number_format($user['total_commute']) }}円</td>

                    <td>
                        @if($user['is_finalized'])
                            <span class="status-finalized">確定済み</span>
                        @else
                            <span class="status-pending">入力中...</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('attendance.show', ['id' => $user['id'], 'month' => $targetMonth]) }}" class="btn-edit">
                            勤務表を編集・確認
                        </a>

                        @if(!$user['is_finalized'])
                            <form action="{{ route('admin.finalize') }}" method="POST" onsubmit="return confirm('この月のデータを確定しますか？')">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                                <input type="hidden" name="month" value="{{ $targetMonth }}">
                                <button type="submit" class="btn-finalize">月次確定</button>
                            </form>
                        @else
                            <form action="{{ route('admin.unfinalize') }}" method="POST" onsubmit="return confirm('確定を解除しますか？')">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                                <input type="hidden" name="month" value="{{ $targetMonth }}">
                                <button type="submit" style="background:#6c757d; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;">確定解除</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>