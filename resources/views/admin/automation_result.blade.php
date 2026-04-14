{{-- resources/views/admin/automation_result.blade.php --}}

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>オートメーション承認結果 ({{ $month }})</title>
    {{-- 共通のCSSを読み込む（ファイル名は適宜調整してください） --}}
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
    <style>
        body { font-family: sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 1200px; margin: 0 auto; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #1976d2; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 1.5em; color: #333; margin: 0; }
        .back-link { color: #1976d2; text-decoration: none; font-weight: bold; }
        .back-link:hover { text-decoration: underline; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; text-align: left; padding: 12px; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-ng { color: #dc3545; font-weight: bold; }
        .error-list { color: #dc3545; font-size: 0.85em; margin: 0; padding-left: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-flex">
        <h2 class="title">🤖 オートメーション承認結果 ({{ $month }})</h2>
        <a href="{{ route('admin.monthly_report') }}" class="back-link">← 月次集計へ戻る</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>社員番号</th>
                <th>氏名</th>
                <th>所属</th>
                <th>総稼働</th>
                <th>総残業</th>
                <th>ステータス</th>
                <th>不正入力・不備リスト</th>
                <th>入力日数</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $res)
            <tr>
                <td>{{ $res['emp_code'] }}</td>
                <td style="font-weight: bold;">{{ $res['name'] }}</td>
                <td>{{ $res['dept'] }}</td>
                <td>{{ number_format($res['total_work'], 2) }}h</td>
                <td>{{ number_format($res['total_ot'], 2) }}h</td>
                <td>
                    <span class="{{ $res['status'] == '承認済み' ? 'status-ok' : 'status-ng' }}">
                        {{ $res['status'] }}
                    </span>
                </td>
                <td>
                    @if(empty($res['errors']))
                        <span style="color: #999;">なし</span>
                    @else
                        <ul class="error-list">
                            @foreach($res['errors'] as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    @endif
                </td>
                <td>
                    {{ $res['filled_days'] ?? 0 }} / {{ $res['threshold'] ?? '-' }}日
                    @if(($res['filled_days'] ?? 0) < ($res['threshold'] ?? 0))
                        <span style="color: red; font-weight: bold;">(不足)</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

</body>
</html>