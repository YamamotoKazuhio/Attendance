<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>給与設定管理</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background: #f9f9f9; color: #333; }
        .header-nav { margin-bottom: 20px; }
        h1 { text-align: center; margin-bottom: 30px; }
        .settings-container { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .section-title { font-size: 18px; font-weight: bold; margin-top: 25px; margin-bottom: 15px; border-bottom: 2px solid #ff9800; padding-bottom: 5px; }
        .form-group { display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; }
        .form-group label { font-weight: bold; width: 40%; }
        .form-group input { width: 50%; padding: 8px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; }
        .btn-submit { display: block; width: 100%; padding: 12px; background: #ff9800; color: #fff; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 30px; }
        .btn-submit:hover { background: #e68900; }
    </style>
</head>
<body>
    <div class="header-nav">
        <a href="{{ route('payment.payroll') }}">← 給与管理一覧へ戻る</a>
    </div>

    <div class="settings-container">
        <h1>給与基本設定</h1>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('payment.settings.update') }}" method="POST">
            @csrf

            <div class="section-title">基本給設定（レベル1〜5）</div>
            @foreach([1, 2, 3, 4, 5] as $lvl)
            <div class="form-group">
                <label for="base_salary_{{ $lvl }}">レベル {{ $lvl }}</label>
                <input type="number" id="base_salary_{{ $lvl }}" name="base_salaries[{{ $lvl }}]" value="{{ $baseSalaries[$lvl] ?? 0 }}" required> 円
            </div>
            @endforeach

            <div class="section-title">昇給設定</div>
            <div class="form-group">
                <label for="raise_per_year">昇給額（年間あたり）</label>
                <input type="number" id="raise_per_year" name="raise_per_year" value="{{ $raisePerYear }}" required> 円/年
            </div>

            <div class="section-title">手当・控除・税金設定</div>
            <div class="form-group">
                <label for="allowance_skill">技能手当</label>
                <input type="number" id="allowance_skill" name="allowances[skill]" value="{{ $allowances['skill'] ?? 50000 }}" required> 円
            </div>
            <div class="form-group">
                <label for="allowance_pos_k">役職手当 (Class_K)</label>
                <input type="number" id="allowance_pos_k" name="allowances[position_k]" value="{{ $allowances['position_k'] ?? 50000 }}" required> 円
            </div>
            <div class="form-group">
                <label for="allowance_pos_b">役職手当 (Class_B)</label>
                <input type="number" id="allowance_pos_b" name="allowances[position_b]" value="{{ $allowances['position_b'] ?? 100000 }}" required> 円
            </div>
            <div class="form-group">
                <label for="resident_tax">住民税（標準月額）</label>
                <input type="number" id="resident_tax" name="allowances[resident_tax]" value="{{ $allowances['resident_tax'] ?? 15000 }}" required> 円
            </div>
            <div class="form-group">
                <label for="care_insurance">介護保険料</label>
                <input type="number" id="care_insurance" name="allowances[care_insurance]" value="{{ $allowances['care_insurance'] ?? 3000 }}" required> 円
            </div>
            <div class="form-group">
                <label for="reserve_fund">積立金</label>
                <input type="number" id="reserve_fund" name="allowances[reserve_fund]" value="{{ $allowances['reserve_fund'] ?? 5000 }}" required> 円
            </div>

            <button type="submit" class="btn-submit">設定を保存する</button>
        </form>
    </div>
</body>
</html>