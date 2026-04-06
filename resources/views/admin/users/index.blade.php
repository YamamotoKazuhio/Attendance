<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>社員情報一覧</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #f4f7f6; padding: 20px; color: #333; }
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        
        .header-area { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .nav-back { text-decoration: none; color: #1976d2; font-weight: bold; font-size: 0.9em; }
        .nav-back:hover { text-decoration: underline; }
        
        .tab-area { margin-bottom: 20px; display: flex; gap: 10px; }
        .tab-btn { padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9em; font-weight: bold; }
        .tab-active { background: #1976d2; color: white; }
        .tab-inactive { background: #e0e0e0; color: #666; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #fff; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 12px 8px; text-align: left; word-break: break-all; font-size: 0.9em; }
        th { background: #f8f9fa; color: #555; font-weight: bold; position: sticky; top: 0; }
        
        /* 列幅の調整 */
        .col-code { width: 100px; }
        .col-name { width: 150px; }
        .col-email { width: 200px; }
        .col-date { width: 120px; }
        .col-years { width: 80px; }
        .col-paid { width: 80px; }
        .col-admin { width: 70px; }
        .col-action { width: 120px; }

        tr:hover { background-color: #f1f8ff; }
        .bg-retired { background-color: #f9f9f9; color: #999; }

        .text-center { text-align: center; }
        
        .badge-admin { 
            color: #d32f2f; 
            font-weight: bold;
            font-size: 1.2em;
        }
        .badge-retired {
            background: #9e9e9e;
            color: white;
            font-size: 0.7em;
            padding: 2px 4px;
            border-radius: 3px;
            margin-left: 4px;
        }

        .btn-action { text-decoration: none; font-weight: bold; margin-right: 8px; }
        .btn-edit { color: #1976d2; }
        .btn-delete { color: #e53935; border: none; background: none; cursor: pointer; padding: 0; font-family: inherit; font-size: inherit; }
        .btn-restore { color: #388e3c; border: none; background: none; cursor: pointer; padding: 0; font-family: inherit; font-size: inherit; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-area">
            <h2 style="margin: 0;">👥 社員情報一覧</h2>
            <a href="{{ route('admin.menu') }}" class="nav-back">← 管理メニューへ戻る</a>
        </div>
        
        <div style="margin: 20px 0; display: flex; justify-content: flex-end;">
            <a href="{{ route('admin.register') }}"
                style="background-color: #28a745; color: white; padding: 10px 22px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; box-shadow: 0 2px 5px rgba(0,0,0,0.15); display: inline-flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">+</span> 新規ユーザー登録
            </a>
        </div>
        
        <div class="tab-area">
            <a href="{{ route('admin.users.index') }}" 
                class="tab-btn {{ request('tab') !== 'all' ? 'tab-active' : 'tab-inactive' }}">
                在職中
            </a>
            <a href="{{ route('admin.users.index', ['tab' => 'all']) }}" 
                class="tab-btn {{ request('tab') === 'all' ? 'tab-active' : 'tab-inactive' }}">
                すべて（退職者含む）
            </a>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="col-code">社員番号</th>
                    <th class="col-name">氏名</th>
                    <th class="col-email">アカウント名</th>
                    <th class="col-date">入社日</th>
                    <th class="col-years">勤続年数</th>
                    <th class="col-paid">有給残数</th>
                    <th class="col-admin text-center">管理者</th>
                    <th class="col-action text-center">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="{{ $user->trashed() ? 'bg-retired' : '' }}">
                    {{-- 社員番号: user_details の値を優先 --}}
                    <td class="text-center">
                        {{ $user->detail->employee_code ?? ($user->employee_code ?? '---') }}
                    </td>

                    {{-- 氏名: user_details の name_kanji を優先 --}}
                    <td>
                        {{ $user->detail->name_kanji ?? ($user->name ?? '---') }}
                        @if($user->trashed())
                            <span class="badge-retired">退職済</span>
                        @endif
                    </td>

                    {{-- アカウント名: usersテーブルのemail --}}
                    <td>{{ $user->email }}</td>

                    {{-- 入社日: Controllerで計算済みの値 --}}
                    <td>{{ $user->formatted_joined_at }}</td>

                    {{-- 勤続年数: Controllerで計算済みの値 --}}
                    <td class="text-right">{{ $user->experience_years }}年</td>

                    {{-- 有給残数: Controllerで計算済みの値 --}}
                    <td class="text-right">{{ $user->remaining_paid }}日</td>

                    {{-- 管理者フラグ --}}
                    <td class="text-center">
                    @if($user->is_admin)
                        <span class="badge-admin">○</span>
                    @endif
                    </td>

                    {{-- 操作ボタン --}}
                    <td class="text-center">
                        @if($user->trashed())
                        <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" style="display:inline;">
                        @csrf
                            <button type="submit" class="btn-restore btn-action" onclick="return confirm('在職状態に戻しますか？')">
                                復元
                            </button>
                        </form>
                        @else
                        <a href="{{ route('user_details.show', $user->id) }}" class="btn-edit btn-action">詳細</a>
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete btn-action" onclick="return confirm('本当に削除（退職処理）しますか？')">
                            削除
                        </button>
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