<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>経費精算表 | 業務管理システム</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/expenses.css') }}">
    <script src="{{ asset('js/encoding.min.js') }}"></script>
    
    <style>
        /* インラインで追加のデザイン調整（必要に応じて外部CSSへ移動してください） */
        body {
            font-family: 'Inter', 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif;
            background-color: #fdf5ef; /* ピーチパフをベースにした淡い背景色 */
            margin: 0;
            padding-bottom: 50px;
        }

        .header-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .main-container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* 入力フォームのカードデザイン */
        .form-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 30px;
            margin-bottom: 40px;
        }

        .user-profile {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #ddd;
            color: #555;
        }

        .input-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
        }

        .input-group label {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: #444;
        }

        input, select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
        }

        input:focus {
            outline: none;
            border-color: #1a73e8;
            box-shadow: 0 0 0 3px rgba(26,115,232,0.1);
        }

        /* 請求額の強調表示 */
        .claim-summary {
            grid-column: 1 / -1;
            background: #f0f7ff;
            padding: 20px;
            border-radius: 8px;
            text-align: right;
            margin-top: 10px;
        }

        .claim-label { font-size: 0.9rem; color: #666; }
        .claim-value { font-size: 2rem; font-weight: 700; color: #1a73e8; margin-left: 10px; }

        /* ステータスバッジ */
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .badge-draft { background: #e0e0e0; color: #616161; }
        .badge-pending { background: #fff3e0; color: #ef6c00; }
        .badge-approved { background: #e8f5e9; color: #2e7d32; }
        .badge-rejected { background: #ffebee; color: #c62828; }

        /* テーブル */
        .report-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .report-table th {
            background-color: #444;
            color: #fff;
            padding: 15px;
            font-size: 0.85rem;
            text-align: center;
        }

        .report-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            text-align: center;
            vertical-align: middle;
        }

        .reject-reason-content {
            background-color: #fffafa;
            color: #d32f2f;
            text-align: left !important;
            font-size: 0.85rem;
            padding: 10px 20px !important;
        }

        .btn-primary { background: #1a73e8; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn-primary:hover { background: #1557b0; }
        .btn-outline { background: white; border: 1px solid #ccc; padding: 8px 15px; border-radius: 6px; cursor: pointer; }
        .btn-outline:hover { background: #f5f5f5; }
    </style>
</head>
<body>

    <nav class="header-nav">
        <div>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-outline">サインアウト</button>
            </form>
            <a href="{{ route('guide_expense.show') }}" class="btn-outline" style="text-decoration:none; margin-left:10px;">📖 ガイド</a>
        </div>
        <div>
            <a href="{{ route('attendance.index') }}" style="text-decoration:none; font-weight:600; color:#1a73e8;">← 勤務表へ</a>
        </div>
    </nav>

    <div class="main-container">
        <h2 style="text-align: center; margin-bottom: 30px; color:#333;">💰 経費精算申請</h2>

        <div class="form-card">
            <div class="user-profile">
                <span><strong>社員番号:</strong> {{ Auth::user()->detail->employee_code ?? '未設定' }}</span>
                <span><strong>氏名:</strong> {{ Auth::user()->detail->name_kanji ?? Auth::user()->name }}</span>
            </div>

            <div class="input-grid">
                <div class="input-group">
                    <label>発生日</label>
                    <input id="occured_date" type="date" value="{{ date('Y-m-d') }}">
                </div>
                <div class="input-group">
                    <label>種別 / 詳細</label>
                    <div style="display:flex; gap:5px;">
                        <select id="category" onchange="updateSubcategory()" style="flex:1;">
                            <option value="交通費">交通費</option>
                            <option value="宿泊費">宿泊費</option>
                            <option value="その他">その他</option>
                        </select>
                        <select id="subcategory" style="flex:1;"></select>
                    </div>
                </div>
                <div class="input-group">
                    <label>出発地</label>
                    <input id="departure" type="text" placeholder="例：前橋駅">
                </div>
                <div class="input-group">
                    <label>目的地</label>
                    <input id="destination" type="text" placeholder="例：東京本社">
                </div>
                <div class="input-group">
                    <label>支払先 / 備考</label>
                    <input id="payee" type="text" placeholder="例：JR東日本">
                </div>
                <div class="input-group">
                    <label>金額 (税込)</label>
                    <input id="amount" type="number" oninput="calc()" placeholder="0">
                </div>
                <div class="input-group">
                    <label>前払い額</label>
                    <input id="prepaid" type="number" oninput="calc()" placeholder="0">
                </div>
                <div class="input-group">
                    <label>領収書</label>
                    <select id="receipt">
                        <option value="あり">あり</option>
                        <option value="なし">なし</option>
                    </select>
                </div>

                <div class="claim-summary">
                    <span class="claim-label">今回の請求額合計:</span>
                    <span class="claim-value">¥ <span id="claimAmount">0</span></span>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; justify-content: center; gap: 15px;">
                <button id="submitBtn" class="btn-primary" onclick="submitData()">申請データを登録する</button>
                <button class="btn-outline" onclick="clearData()">クリア</button>
                
                @if(Auth::user()->is_admin)
                   <button type="button" style="background:darkred; color:white;" class="btn-primary" onclick="location.href='{{ route('expenses.admin') }}'">
                        🛡️ 承認パネル
                    </button>
                @endif
            </div>
        </div>

        <h3 style="margin-bottom: 15px; color:#555;">📋 直近の申請履歴（30件）</h3>
        <table class="report-table">
            <thead>
                <tr>
                    <th>状態</th><th>発生日</th><th>内容</th><th>出発 / 目的</th><th>金額</th><th>請求額</th><th>操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $ex)
                <tr>
                    <td>
                        @if($ex->status == 0)
                            <span class="badge badge-draft">下書き</span>
                        @elseif($ex->status == 1)
                            <span class="badge badge-pending">承認待ち</span>
                        @elseif($ex->status == 2)
                            <span class="badge badge-approved">承認済み</span>
                        @elseif($ex->status == 3)
                            <span class="badge badge-rejected">却下</span>
                        @endif
                    </td>
                    <td>{{ $ex->occured_date }}</td>
                    <td><strong>{{ $ex->category }}</strong><br><small>{{ $ex->subcategory }}</small></td>
                    <td><small>{{ $ex->departure ?? '-' }}<br>↓<br>{{ $ex->destination ?? '-' }}</small></td>
                    <td style="text-align:right;">{{ number_format($ex->amount) }}円</td>
                    <td style="text-align:right; font-weight:700; color:#1a73e8;">{{ number_format($ex->claim_amount) }}円</td>
                    <td>
                        @if($ex->status == 0 || $ex->status == 3)
                            <button class="status-btn btn-apply" style="color:#1a73e8; background:none; border:none; cursor:pointer; font-weight:bold;" onclick="updateStatus({{ $ex->id }}, 1)">申請</button>
                            <button class="status-btn btn-edit" style="color:#ef6c00; background:none; border:none; cursor:pointer;" onclick='editExpense(@json($ex))'>編集</button>
                            <button class="status-btn btn-delete" style="color:#c62828; background:none; border:none; cursor:pointer;" onclick="deleteExpense({{ $ex->id }})">削除</button>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @if($ex->status == 3)
                <tr>
                    <td colspan="7" class="reject-reason-content">
                        <strong>【却下理由】</strong> {{ $ex->reject_reason }}
                    </td>
                </tr>
                @endif
                @empty
                <tr><td colspan="7">登録済みの経費データはありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        const options = {
            "交通費": ["鉄道", "バス", "タクシー", "ガソリン代"],
            "宿泊費": ["宿泊費"],
            "その他": ["レンタカー代", "物品購入", "会議費"]
        };

        function updateSubcategory() {
            const cat = document.getElementById('category').value;
            const sub = document.getElementById('subcategory');
            sub.innerHTML = '';
            options[cat].forEach(opt => {
                const o = document.createElement('option');
                o.value = opt; o.textContent = opt;
                sub.appendChild(o);
            });
        }

        function calc() {
            const a = parseInt(document.getElementById('amount').value) || 0;
            const p = parseInt(document.getElementById('prepaid').value) || 0;
            document.getElementById('claimAmount').textContent = (a - p).toLocaleString();
        }

        async function submitData() {
            const payload = {
                occured_date: document.getElementById('occured_date').value,
                category: document.getElementById('category').value,
                subcategory: document.getElementById('subcategory').value,
                departure: document.getElementById('departure').value,
                destination: document.getElementById('destination').value,
                payee: document.getElementById('payee').value,
                amount: document.getElementById('amount').value,
                prepaid: document.getElementById('prepaid').value,
                receipt: document.getElementById('receipt').value,
            };

            const res = await fetch("{{ route('expenses.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                alert('データを登録しました。');
                location.reload();
            }
        }

        async function updateStatus(id, status) {
            if (!confirm('この経費を承認申請しますか？')) return;

            const res = await fetch(`/expenses/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: status })
            });

            if (res.ok) {
                alert('申請を完了しました。');
                location.reload();
            }
        }

        async function deleteExpense(id) {
            if (!confirm('このデータを削除しますか？')) return;
            const res = await fetch(`/expenses/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            if (res.ok) location.reload();
        }

        function editExpense(ex) {
            document.getElementById('occured_date').value = ex.occured_date;
            document.getElementById('category').value = ex.category;
            updateSubcategory();
            document.getElementById('subcategory').value = ex.subcategory;
            document.getElementById('departure').value = ex.departure;
            document.getElementById('destination').value = ex.destination;
            document.getElementById('payee').value = ex.payee;
            document.getElementById('amount').value = ex.amount;
            document.getElementById('prepaid').value = ex.prepaid;
            calc();
            document.getElementById('receipt').value = ex.receipt;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function clearData() {
            if(confirm("入力をクリアしますか？")) location.reload();
        }

        window.onload = updateSubcategory;
    </script>
</body>
</html>