{{--  resources/views/user_details/show.blade.php --}}

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー詳細情報</title>
    <link rel="stylesheet" href="{{ asset('css/Attendance.css') }}">
    <style>
        body { background-color: #f4f7f6; padding: 20px; font-family: "Helvetica Neue", Arial, sans-serif; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { border-bottom: 2px solid #eee; margin-bottom: 20px; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .form-control:disabled { background-color: #f9f9f9; color: #888; }
        .section-title { margin-top: 30px; padding: 8px; background: #e9ecef; border-radius: 4px; font-size: 1.1em; font-weight: bold; }
        .btn-save { background-color: #28a745; color: white; border: none; padding: 12px 25px; border-radius: 4px; cursor: pointer; font-size: 1em; font-weight: bold; width: 100%; margin-top: 20px; }
        .btn-save:hover { background-color: #218838; }
        .alert-success { background-color: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-danger { background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .password-notice { font-size: 0.85em; color: #dc3545; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2 style="margin:0;">👤 ユーザー詳細情報</h2>
        @if(Auth::user()->is_admin)
            {{-- 管理者の場合：社員情報一覧へ戻る --}}
            <a href="{{ route('admin.users.index') }}" style="text-decoration: none; color: #007bff; font-weight: bold;">
                ← 社員情報一覧へ戻る
            </a>
        @else
            {{-- 一般ユーザーの場合：勤務表へ戻る --}}
            <a href="{{ route('attendance.index') }}" style="text-decoration: none; color: #007bff; font-weight: bold;">
                ← 勤務表に戻る
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert-danger">
            <ul style="margin:0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('user_details.update', $user->id) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="section-title">基本アカウント情報</div>
        <div style="padding: 10px;">
            <div class="form-group">
                <label>社員番号</label>
                <input type="text" name="employee_code" value="{{ old('employee_code', $user->detail->employee_code ?? '') }}" 
                    class="form-control" {{ Auth::user()->is_admin ? '' : 'disabled' }}>
            </div>

            <div class="form-group">
                <label>氏名（漢字）</label>
                <input type="text" name="name_kanji" value="{{ old('name_kanji', $user->detail->name_kanji ?? $user->name) }}" 
                    class="form-control" {{ Auth::user()->is_admin ? '' : 'disabled' }}>
            </div>

            <div class="form-group">
                <label>メールアドレス (e-mail)</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
            </div>
        </div>

        <div class="section-title">パスワード変更</div>
        <div style="padding: 10px; border: 1px solid #ffeeba; background-color: #fffaf0; border-radius: 4px; margin-top: 10px;">
            <p class="password-notice">※変更する場合のみ入力してください。変更しない場合は空欄のままにしてください。</p>
            <div class="form-group">
                <label>新しいパスワード</label>
                <input type="password" name="password" class="form-control" placeholder="8文字以上で入力">
            </div>
            <div class="form-group">
                <label>新しいパスワード（確認用）</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="もう一度入力">
            </div>
        </div>

        <div class="section-title">連絡先・詳細情報</div>
        <div style="padding: 10px;">
            <div class="form-group">
                <label>住所（市町村名まで記入）</label>
                <input type="text" name="address_city" value="{{ old('address_city', $user->detail->address_city ?? '') }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label>電話番号</label>
                <input type="text" name="phone_number" value="{{ old('phone_number', $user->detail->phone_number ?? '') }}" class="form-control" required>
            </div>

            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 2;">
                    <label>緊急連絡先（電話番号）</label>
                    <input type="text" name="emergency_contact" value="{{ old('emergency_contact', $user->detail->emergency_contact ?? '') }}" class="form-control" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>本人との続柄</label>
                    <input type="text" name="emergency_relationship" value="{{ old('emergency_relationship', $user->detail->emergency_relationship ?? '') }}" class="form-control" required placeholder="例：父、妻">
                </div>
            </div>

            <div class="form-group">
                <label>入社日</label>
                <input type="date" name="joined_at" value="{{ old('joined_at', isset($user->detail->joined_at) ? $user->detail->joined_at->format('Y-m-d') : '') }}" 
                    class="form-control" {{ Auth::user()->is_admin ? '' : 'disabled' }}>
            </div>
        </div>
        @if(Auth::user()->is_admin)
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">システム権限設定</div>
                <div class="card-body">
                    <div class="mb-3 d-flex align-items-center">
                        <label class="me-3">現在のステータス:</label>
                        @if($user->is_admin)
                            <span class="badge bg-danger">管理者</span>
                        @else
                            <span class="badge bg-secondary">一般ユーザー</span>
                        @endif
                    </div>
                    @if(Auth::id() !== $user->id)
                    <div class="form-group">
                        <label>権限を変更する</label>
                        <select name="is_admin" class="form-select mt-1">
                            <option value="0" {{ !$user->is_admin ? 'selected' : '' }}>一般ユーザー</option>
                            <option value="1" {{ $user->is_admin ? 'selected' : '' }}>管理者</option>
                        </select>
                        <small class="text-muted">※保存ボタンを押すと確定します。</small>
                    </div>
                    @else
                    <p class="text-muted small">※自分自身の権限は変更できません。</p>
                    <input type="hidden" name="is_admin" value="{{ $user->is_admin }}">
                @endif
            </div>
        </div>
        @endif
        <button type="submit" class="btn-save">情報を更新する</button>
    </form>
</div>

</body>
</html>