<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>休暇・残業申請承認</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f7f6; padding: 20px; }
        .container { max-width: 1100px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { border-left: 5px solid #ff9800; padding-left: 15px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; color: #666; white-space: nowrap; }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; font-size: 14px; }
        .btn-approve { background-color: #4caf50; color: white; margin-right: 5px; }
        .btn-approve:hover { background-color: #43a047; }
        .btn-reject { background-color: #f44336; color: white; }
        .btn-reject:hover { background-color: #e53935; }
        .btn-back { background-color: #6c757d; color: white; margin-bottom: 20px; }
        .btn-back:hover { background-color: #5a6268; }
        .reason-text { font-size: 0.9em; color: #666; font-style: italic; max-width: 250px; word-wrap: break-word; }
        .empty-msg { text-align: center; padding: 40px; color: #999; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .emp-code { font-family: monospace; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ route('admin.menu') }}" class="btn btn-back">← 管理メニューへ戻る</a>
        
        <h2>休暇・残業 承認待ち一覧</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($applications->isEmpty())
            <div class="empty-msg">現在、未処理の申請はありません。</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>申請日時</th>
                        <th>社員番号</th>
                        <th>氏名</th>
                        <th>対象日</th>
                        <th>種別</th>
                        <th>日数/時間</th>
                        <th>理由</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $app)
                    <tr>
                        <td>{{ $app->created_at->format('m/d H:i') }}</td>
                        {{-- 社員番号を表示（UserDetailから取得） --}}
                        <td class="emp-code">{{ $app->user->detail->employee_code ?? '未設定' }}</td>
                        {{-- 氏名をUserDetailの漢字氏名優先で表示 --}}
                        <td><strong>{{ $app->user->detail->name_kanji ?? $app->user->name }}</strong></td>
                        <td>{{ $app->target_date }}</td>
                        <td>
                            <span style="padding: 2px 6px; border-radius: 4px; background: #fff3e0; color: #e65100; font-weight: bold; font-size: 0.9em;">
                                {{ $app->type }}
                            </span>
                        </td>
                        <td>{{ $app->days_count }}</td>
                        <td class="reason-text">{{ $app->reason ?? '-' }}</td>
                        <td>
                            <div style="display: flex;">
                                <form action="{{ route('admin.leave_approve', $app->id) }}" method="POST" onsubmit="return confirm('この申請を承認し、勤務表に反映しますか？')">
                                    @csrf
                                    <button type="submit" class="btn btn-approve">承認</button>
                                </form>
                                
                                <form action="{{ route('admin.leave_reject', $app->id) }}" method="POST" onsubmit="return confirm('この申請を却下しますか？')">
                                    @csrf
                                    <button type="submit" class="btn btn-reject">却下</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>