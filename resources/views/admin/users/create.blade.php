<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規社員登録</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #f4f7f6; padding: 20px; color: #333; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header-area { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .nav-back { text-decoration: none; color: #1976d2; font-weight: bold; font-size: 0.9em; }
        .nav-back:hover { text-decoration: underline; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 6px; font-size: 0.9em; color: #555; }
        input[type="text"], input[type="email"], input[type="password"], input[type="date"], select {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1em; box-sizing: border-box;
        }
        .error-msg { color: #d32f2f; font-size: 0.85em; margin-top: 4px; }
        .btn-submit { background-color: #28a745; color: white; padding: 12px 24px; border: none; border-radius: 6px; font-weight: bold; font-size: 1em; cursor: pointer; width: 100%; }
        .btn-submit:hover { background-color: #218838; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-area">
            <h2 style="margin: 0;">➕ 新規社員登録</h2>
            <a href="{{ route('admin.users.index') }}" class="nav-back">← 社員一覧へ戻る</a>
        </div>

        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>氏名（漢字）</label>
                <input type="text" name="name_kanji" value="{{ old('name_kanji') }}" required>
                @error('name_kanji') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>表示名 / アカウント名（通常は氏名など）</label>
                <input type="text" name="name" value="{{ old('name') }}" required>
                @error('name') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>社員番号</label>
                <input type="text" name="employee_code" value="{{ old('employee_code') }}" placeholder="例: SYS004" required>
                @error('employee_code') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>メールアドレス (ログインID)</label>
                <input type="email" name="email" value="{{ old('email') }}" required>
                @error('email') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>パスワード (8文字以上)</label>
                <input type="password" name="password" required>
                @error('password') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>パスワード（確認用）</label>
                <input type="password" name="password_confirmation" required>
            </div>

            <div class="form-group">
                <label>所属部署 (※システム部を選択すると自動で管理者権限が付与されます)</label>
                <select name="department" required>
                    <option value="">-- 部署を選択してください --</option>
                    @foreach($departments as $key => $label)
                        <option value="{{ $key }}" {{ old('department') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('department') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>職域 (ロール)</label>
                <select name="role" required>
                    <option value="">-- 職域を選択してください --</option>
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>入社年月日</label>
                <input type="date" name="joined_at" value="{{ old('joined_at') }}" required>
                @error('joined_at') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>現住所（市町村）</label>
                <input type="text" name="address_city" value="{{ old('address_city') }}" placeholder="例: 東京都千代田区" required>
                @error('address_city') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>本人連絡先（電話番号）</label>
                <input type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="例: 090-0000-0000" required>
                @error('phone_number') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>緊急連絡先</label>
                <input type="text" name="emergency_contact" value="{{ old('emergency_contact') }}" placeholder="例: 090-9999-9999" required>
                @error('emergency_contact') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>緊急連絡先 本人との関係</label>
                <input type="text" name="emergency_relationship" value="{{ old('emergency_relationship') }}" placeholder="例: 配偶者" required>
                @error('emergency_relationship') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn-submit">登録する</button>
            </div>
        </form>
    </div>
</body>
</html>