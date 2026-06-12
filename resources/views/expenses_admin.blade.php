{{-- resources/views/expenses_admin.blade.php --}}

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>管理者専用 - 経費承認パネル</title>
    <link rel="stylesheet" href="{{ asset('css/expenses.css') }}">
    <style>
        body { background-color: #fff5f5; padding: 20px; font-family: sans-serif; }
        .admin-container { max-width: 1400px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 2px solid darkred; }
        
        .admin-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid darkred; padding-bottom: 15px; margin-bottom: 20px; }
        
        /* 戻るボタン */
        .back-btn { background-color: #666; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; }
        .back-btn:hover { background-color: #444; }

        /* 検索エリア */
        .search-area { margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; display: flex; align-items: center; gap: 10px; }
        .search-input { width: 350px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }

        /* テーブルスタイル */
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .admin-table th, .admin-table td { border: 1px solid #ccc; padding: 12px 8px; text-align: center; font-size: 13px; }
        .admin-table th { background-color: #ffebee; color: darkred; white-space: nowrap; }
        .expense-row:hover { background-color: #fffde7; }
        
        /* ボタン類 */
        .status-btn { padding: 6px 14px; cursor: pointer; border-radius: 4px; border: none; font-weight: bold; margin: 2px; transition: 0.2s; }
        .btn-approve { background-color: #4CAF50; color: white; }
        .btn-approve:hover { background-color: #388E3C; }
        .btn-reject { background-color: #f44336; color: white; }
        .btn-reject:hover { background-color: #d32f2f; }
        
        /* ステータステキスト */
        .approved { color: #2e7d32; font-weight: bold; }
        .rejected { color: #d32f2f; font-weight: bold; }
        .reason-text { font-size: 11px; color: #d32f2f; display: block; margin-top: 5px; text-align: left; background: #fff0f0; padding: 4px; border-radius: 2px; }
        
        .text-right { text-align: right; padding-right: 15px !important; }
        .text-bold { font-weight: bold; }
    </style>
</head>
<body>

<div class="admin-container">
    <div class="admin-header">
        <div style="display: flex; gap: 20px; align-items: center;">
            <a href="{{ route('admin.menu') }}" class="back-btn" style="text-decoration: none;">
                ← 管理者専用メニューに戻る
            </a>
            <h2 style="color: darkred; margin: 0;">【管理者専用】経費承認パネル</h2>
        </div>
        <button onclick="location.reload()" style="padding: 8px 20px; cursor: pointer; border-radius: 4px; background: white; border: 1px solid #ccc;">最新の情報に更新</button>
    </div>

    <div class="search-area">
        <span style="font-weight: bold; color: #444;">絞り込み抽出:</span>
        <input type="text" id="adminSearchInput" onkeyup="filterAdminTable()" placeholder="社員番号 または 氏名で検索..." class="search-input">
        <span style="font-size: 12px; color: #888;">※入力すると即座にリストが更新されます</span>
    </div>

    <table class="admin-table" id="adminExpenseTable">
        <thead>
            <tr>
                <th style="width: 150px;">申請処理</th>
                <th>社員番号</th>
                <th>氏名</th>
                <th>発生日</th>
                <th>種別 / 詳細</th>
                <th>出発地 / 目的地</th>
                <th>支払先</th>
                <th class="text-right">金額</th>
                <th class="text-right">請求額</th>
                <th>領収書</th>
            </tr>
        </thead>
        <tbody>
            @forelse($adminExpenses as $ae)
            <tr class="expense-row">
                <td>
                    @if($ae->status == 0 || $ae->status == 1)
                        <button class="status-btn btn-approve" onclick="adminUpdateStatus({{ $ae->id }}, 2)">承認</button>
                        <button class="status-btn btn-reject" onclick="adminUpdateStatus({{ $ae->id }}, 3)">却下</button>
                    @elseif($ae->status == 2)
                        <span class="approved">承認済み</span>
                    @elseif($ae->status == 3)
                        <span class="rejected">却下済み</span>
                        @if($ae->reject_reason)
                            <span class="reason-text">理由: {{ $ae->reject_reason }}</span>
                        @endif
                    @endif
                </td>
                {{-- 管理者が登録した詳細情報を参照 --}}
                <td class="search-target-id">{{ $ae->user->detail->employee_code ?? '未設定' }}</td>
                <td class="search-target-name" style="font-weight: bold;">{{ $ae->user->detail->name_kanji ?? ($ae->user->name ?? '不明') }}</td>
                <td>{{ $ae->occured_date }}</td>
                <td>{{ $ae->category }}<br><small style="color:#666;">{{ $ae->subcategory }}</small></td>
                <td>{{ $ae->departure ?? '-' }}<br>→ {{ $ae->destination ?? '-' }}</td>
                <td>{{ $ae->payee }}</td>
                <td class="text-right">{{ number_format($ae->amount) }}</td>
                <td class="text-right text-bold" style="color: #d32f2f;">{{ number_format($ae->claim_amount) }}</td>
                <td>
                    <span style="color: {{ $ae->receipt === 'あり' ? 'green' : '#999' }};">
                        {{ $ae->receipt }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="padding: 50px; color: #999; font-size: 16px;">
                    現在、未処理の申請データはありません。
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    /**
     * リアルタイムフィルタリング機能
     */
    function filterAdminTable() {
        const input = document.getElementById('adminSearchInput');
        const filter = input.value.toLowerCase();
        const rows = document.getElementsByClassName('expense-row');

        for (let i = 0; i < rows.length; i++) {
            const empIdElement = rows[i].querySelector('.search-target-id');
            const empNameElement = rows[i].querySelector('.search-target-name');
            
            if (empIdElement && empNameElement) {
                const empId = empIdElement.textContent.toLowerCase();
                const empName = empNameElement.textContent.toLowerCase();

                if (empId.includes(filter) || empName.includes(filter)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    }

    /**
     * 承認・却下ステータス更新処理
     */
    async function adminUpdateStatus(id, status) {
        let rejectReason = null;
        
        if (status === 3) {
            rejectReason = prompt('却下理由を入力してください（必須）：');
            if (rejectReason === null) return; // キャンセル
            if (rejectReason.trim() === "") {
                alert('却下には理由の入力が必要です。');
                return;
            }
        } else {
            if (!confirm('この経費申請を承認してもよろしいですか？')) return;
        }

        try {
            const response = await fetch(`/expenses/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: status,
                    reject_reason: rejectReason
                })
            });

            const result = await response.json();

            if (response.ok) {
                alert(status === 2 ? '承認が完了しました。' : '申請を却下しました。');
                location.reload();
            } else {
                alert('エラーが発生しました: ' + (result.error || result.message || 'サーバーエラー'));
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            alert('通信に失敗しました。ネットワーク環境を確認してください。');
        }
    }
</script>

</body>
</html>