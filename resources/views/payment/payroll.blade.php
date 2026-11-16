<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>給与管理一覧 - {{ $targetMonth }}分</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background: #f9f9f9; color: #333; }
        .header-nav { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .header-nav a { text-decoration: none; color: #007bff; font-weight: bold; }
        .header-nav a:hover { text-decoration: underline; }
        .btn-settings { padding: 8px 15px; background: #28a745; color: #fff !important; border-radius: 4px; font-weight: bold; }
        .btn-settings:hover { background: #218838; text-decoration: none !important; }
        h1 { text-align: center; margin-bottom: 20px; }
        .search-container { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; align-items: center; }
        .search-group { display: flex; flex-direction: column; font-size: 13px; }
        .search-group label { margin-bottom: 4px; font-weight: bold; }
        .search-group input, .search-group select { padding: 6px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px; }
        .search-actions { display: flex; gap: 10px; align-self: flex-end; }
        .search-actions button { padding: 7px 15px; background: #ff9800; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .search-actions a { padding: 7px 15px; background: #6c757d; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .table-responsive { width: 100%; overflow-x: auto; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 10px; }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; font-size: 13px; }
        th, td { border: 1px solid #ddd; padding: 10px 8px; text-align: right; }
        th:nth-child(1), th:nth-child(2), th:nth-child(3),
        td:nth-child(1), td:nth-child(2), td:nth-child(3) { text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; }
        tr:hover { background-color: #f1f1f1; }
    </style>
</head>
<body>
    <div class="header-nav">
        <a href="{{ route('payment.admin') }}">← 支払管理メニューへ戻る</a>
        <a href="{{ route('payment.settings') }}" class="btn-settings">⚙️ 給与設定</a>
    </div>

    <h1>給与管理一覧 ({{ $targetMonth }}分)</h1>

    {{-- 検索フォーム --}}
    <div class="search-container">
        <form method="GET" action="{{ route('payment.payroll') }}" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
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
                <a href="{{ route('payment.payroll') }}">リセット</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>部署名</th>
                    <th>社員番号</th>
                    <th>氏名</th>
                    <th>基本給</th>
                    <th>技能手当</th>
                    <th>役職手当</th>
                    <th>残業手当</th>
                    <th>通勤手当</th>
                    <th>不就労控除</th>
                    <th>総支給額</th>
                    <th>所得税</th>
                    <th>住民税</th>
                    <th>社会保険料</th>
                    <th>介護保険料</th>
                    <th>積立金</th>
                    <th>銀行振込額</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrollData as $row)
                <tr>
                    <td>{{ $row['department'] }}</td>
                    <td>{{ $row['employee_code'] }}</td>
                    <td>{{ $row['name_kanji'] }}</td>

                    @if($row['is_completed'])
                        {{-- 承認完了している場合：通常の給与金額を表示 --}}
                        <td>{{ number_format($row['base_salary']) }}円</td>
                        <td>{{ number_format($row['skill_allowance']) }}円</td>
                        <td>{{ number_format($row['position_allowance']) }}円</td>
                        <td>{{ number_format($row['overtime_allowance']) }}円</td>
                        <td>{{ number_format($row['commute_allowance']) }}円</td>
                        <td>-{{ number_format($row['total_deduction']) }}円</td>
                        <td><strong>{{ number_format($row['gross_pay']) }}円</strong></td>
                        <td>{{ number_format($row['income_tax']) }}円</td>
                        <td>{{ number_format($row['resident_tax']) }}円</td>
                        <td>{{ number_format($row['social_insurance']) }}円</td>
                        <td>{{ number_format($row['care_insurance']) }}円</td>
                        <td>{{ number_format($row['reserve_fund']) }}円</td>
                        <td><strong>{{ number_format($row['net_pay']) }}円</strong></td>
                    @else
                        {{-- 登録未完の場合：各項目の代わりに「登録未完」を表示（または colspan でつなぐ） --}}
                        <td colspan="13" style="text-align: center; color: #d9534f; font-weight: bold; background-color: #fdf7f7;">
                            ⚠️ 登録未完（未承認または未確定の勤務データがあります）
                        </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="16" style="text-align: center;">該当するデータが見つかりませんでした。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>