<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>経費支払管理 - 承認済み一覧</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background: #f9f9f9; color: #333; }
        .header-nav { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .header-nav a { text-decoration: none; color: #007bff; font-weight: bold; }
        .header-nav a:hover { text-decoration: underline; }
        h1 { text-align: center; margin-bottom: 20px; }
        .search-container { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; align-items: center; }
        .search-group { display: flex; flex-direction: column; font-size: 13px; }
        .search-group label { margin-bottom: 4px; font-weight: bold; }
        .search-group input, .search-group select { padding: 6px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px; }
        .search-actions { display: flex; gap: 10px; align-self: flex-end; }
        .search-actions button { padding: 7px 15px; background: #1a73e8; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .search-actions a { padding: 7px 15px; background: #6c757d; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .table-responsive { width: 100%; overflow-x: auto; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 10px; }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; font-size: 13px; }
        th, td { border: 1px solid #ddd; padding: 12px 10px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; text-align: center; }
        td:nth-child(6) { text-align: right; } /* 支払金額 */
        tr:hover { background-color: #f1f1f1; }
        .total-box { margin-top: 15px; text-align: right; font-size: 16px; font-weight: bold; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="header-nav">
        <a href="{{ route('payment.admin') }}">← 支払管理メニューへ戻る</a>
    </div>

    <h1>経費支払管理（承認済み一覧）</h1>

    {{-- 検索フォーム --}}
    <div class="search-container">
        <form method="GET" action="{{ route('payment.expenses.index') }}" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
            <div class="search-group">
                <label for="month">対象月</label>
                <input type="month" id="month" name="month" value="{{ $targetMonth }}">
            </div>

            <div class="search-group">
                <label for="department">部署名</label>
                <select id="department" name="department">
                    <option value="">すべての部署</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ $searchDepartment === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>

            <div class="search-group">
                <label for="employee_code">社員番号</label>
                <input type="text" id="employee_code" name="employee_code" value="{{ $searchEmployeeCode }}" placeholder="例: EMP001">
            </div>

            <div class="search-group">
                <label for="name">氏名</label>
                <input type="text" id="name" name="name" value="{{ $searchName }}" placeholder="例: 山田">
            </div>

            <div class="search-actions">
                <button type="submit">検索</button>
                <a href="{{ route('payment.expenses.index') }}">リセット</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>発生日</th>
                    <th>部署名</th>
                    <th>社員番号</th>
                    <th>氏名</th>
                    <th>種別 / 詳細</th>
                    <th>支払金額 (請求額)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $ex)
                <tr>
                    <td style="text-align: center;">{{ $ex->occured_date }}</td>
                    <td>{{ optional($ex->user->detail)->department ?? '-' }}</td>
                    <td style="text-align: center;">{{ optional($ex->user->detail)->employee_code ?? '-' }}</td>
                    <td>{{ optional($ex->user->detail)->name_kanji ?? optional($ex->user)->name }}</td>
                    <td>
                        <strong>{{ $ex->category }}</strong> 
                        @if($ex->subcategory)
                            <span style="color: #666;">/ {{ $ex->subcategory }}</span>
                        @endif
                        @if($ex->departure || $ex->destination)
                            <br><small style="color: #888;">{{ $ex->departure ?? '-' }} → {{ $ex->destination ?? '-' }}</small>
                        @endif
                    </td>
                    <td><strong>{{ number_format($ex->claim_amount) }}円</strong> <small style="color: #666;">(税込: {{ number_format($ex->amount) }}円)</small></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #999; padding: 30px;">
                        条件に一致する承認済みの経費データはありません。
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="total-box">
        合計支払金額: <span style="color: #1a73e8;">{{ number_format($expenses->sum('claim_amount')) }} 円</span>
        <span style="font-size: 12px; color: #666; font-weight: normal; margin-left: 10px;">（件数: {{ $expenses->count() }}件）</span>
    </div>
</body>
</html>