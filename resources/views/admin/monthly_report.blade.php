<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $targetMonth }} 月次集計一覧</title>
    <link rel="stylesheet" href="{{ asset('css/Attendance.css') }}">
    <style>
        body { background-color: #f8f9fa; font-family: sans-serif; padding: 20px; }
        .report-container { max-width: 1400px; margin: 0 auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .report-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .report-table { width: 100%; border-collapse: collapse; background: white; font-size: 13px; table-layout: fixed; }
        .report-table th, .report-table td { border: 1px solid #ccc; padding: 12px 8px; text-align: center; word-break: break-all; }
        .report-table th { background: #007bff; color: white; position: sticky; top: 0; z-index: 10; }
        .text-right { text-align: right; }
        .badge-finalized { background-color: #6c757d; color: white; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 12px; display: block; margin-top: 5px; }
        .btn-finalize { background-color: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; }
        .btn-reopen { background-color: #ffc107; color: #212529; border: 1px solid #d39e00; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; }
        .remaining-paid { font-weight: bold; background-color: #fff9db; }
        .alert-success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #c3e6cb; }
        
        /* 列幅の調整 */
        .col-emp { width: 80px; }
        .col-name { width: 150px; }
        .col-paid { width: 100px; }
        .col-work { width: 90px; }
        .col-commute { width: 100px; }
        .col-action { width: 160px; }
    </style>
</head>
<body>

<div class="report-container">
    <div class="report-header">
        <h2 style="margin: 0;">月次稼働集計 ({{ $targetMonth }})</h2>
        <a href="{{ route('admin.menu') }}" style="text-decoration: none; background: #6c757d; color: white; padding: 8px 16px; border-radius: 4px;">管理メニューへ戻る</a>
    </div>

    {{-- フラッシュメッセージ表示エリア --}}
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div style="margin-bottom: 20px; background: #eee; padding: 15px; border-radius: 4px; display: flex; align-items: center; gap: 20px;">
        <form action="{{ route('admin.monthly_report') }}" method="GET" style="margin: 0;">
            <label style="font-weight: bold;">表示月選択：</label>
            <input type="month" name="month" value="{{ substr($targetMonth, 0, 7) }}" onchange="this.form.submit()" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
        </form>

        <form action="{{ route('admin.monthly.automate') }}" method="POST" onsubmit="return confirm('選択した日数以上の入力があるユーザーを一括承認します。よろしいですか？')" style="margin: 0; display: flex; align-items: center; gap: 10px;">
        @csrf
        <input type="hidden" name="month" value="{{ $targetMonth }}">
        
        <label style="font-weight: bold;">承認基準：</label>
        <select name="threshold_days" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
            <option value="15">15日以上入力</option>
            <option value="20" selected>20日以上入力</option>
            <option value="25">25日以上入力</option>
        </select>

        <button type="submit" style="background-color: #ff5722; color: white; padding: 10px 20px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
            🚀 オートメーション一括承認
        </button>
    </form>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th class="col-emp">社員番号</th>
                <th class="col-name">氏名</th>
                <th class="col-paid">今月の有給取得日数</th>
                <th class="col-paid">有給残日数</th>
                <th class="col-work">総稼働</th>
                <th class="col-work">残業</th>
                <th class="col-commute">通勤費</th>
                <th class="col-action">アクション</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                {{-- 社員番号 --}}
                <td>{{ $user['emp_id'] }}</td>
                
                {{-- 氏名 --}}
                <td style="font-weight: bold; text-align: left; padding-left: 10px;">{{ $user['name'] }}</td>
                
                {{-- 今月の有給取得日数（1.0 or 0.5 の合算） --}}
                <td>{{ number_format($user['monthly_paid'], 1) }}日</td>
                
                {{-- 有給残日数（マスタ値） --}}
                <td class="remaining-paid" style="color: {{ $user['remaining_paid'] <= 2 ? 'red' : 'black' }};">
                    {{ number_format($user['remaining_paid'], 1) }}日
                </td>
                
                {{-- 総稼働 --}}
                <td class="text-right">{{ number_format($user['total_work'], 2) }}h</td>
                
                {{-- 残業 --}}
                <td class="text-right">{{ number_format($user['total_ot'], 2) }}h</td>
                
                {{-- 通勤費 --}}
                <td class="text-right">{{ number_format($user['total_commute']) }}円</td>
                
                {{-- アクション --}}
                <td>
                    <div style="display: flex; flex-direction: column; gap: 8px; align-items: center;">
                        <a href="{{ route('attendance.index', ['search_query' => $user['emp_id'], 'month' => $targetMonth]) }}" style="font-size: 12px; text-decoration: underline; color: #0056b3; font-weight: bold;">
                            勤務表を編集・確認
                        </a>
                        
                        @if($user['is_finalized'])
                            <form action="{{ route('admin.reopen') }}" method="POST" style="width: 100%;">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                                <input type="hidden" name="month" value="{{ $targetMonth }}">
                                <button type="submit" class="btn-reopen" onclick="return confirm('この月の確定を解除して編集可能にしますか？')">
                                    確定解除
                                </button>
                            </form>
                            <span class="badge-finalized">確定済み</span>
                        @else
                            <form action="{{ route('admin.finalize') }}" method="POST" style="width: 100%;">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                                <input type="hidden" name="month" value="{{ $targetMonth }}">
                                <button type="submit" class="btn-finalize" onclick="return confirm('この月の内容を確定させますか？')">
                                    月次確定
                                </button>
                            </form>
                            <span style="font-size: 11px; color: #17a2b8; font-weight: bold;">入力中...</span>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

</body>
</html>