<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>経費精算表</title>
    <link rel="stylesheet" href="{{ asset('css/expenses.css') }}">
    <script src="{{ asset('js/encoding.min.js') }}"></script>
    <style>
        .status-btn { padding: 4px 8px; margin: 2px; cursor: pointer; font-size: 11px; border-radius: 4px; }
        .btn-apply { background-color: #e3f2fd; border: 1px solid #2196f3; color: #1976d2; }
        .btn-edit { background-color: #fff3e0; border: 1px solid #ff9800; color: #e65100; }
        .btn-delete { background-color: #ffebee; border: 1px solid #f44336; color: #c62828; }
        .btn-admin-panel { background-color: darkred; color: white; font-weight: bold; border: none; padding: 10px 20px; cursor: pointer; }
        
        .report-table { width: 95%; margin: 20px auto; border-collapse: collapse; background: white; }
        .report-table th, .report-table td { border: 1px solid black; padding: 8px; text-align: center; }
        .text-right { text-align: right; }
        .approved { color: blue; font-weight: bold; }
        .rejected-text { color: red; font-weight: bold; }
        .pending { color: orange; font-weight: bold; }
        
        /* 却下理由表示行のスタイル */
        .reject-reason-row { background-color: #fff0f0; }
        .reject-reason-content { text-align: left !important; padding: 10px 20px !important; color: #d32f2f; border-top: none !important; }
    </style>
</head>
<body>
    <div class="top-left">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="big-text">サインアウト</button>
        </form>
    </div>

    <div class="top-right">
        <a href="{{ route('attendance.index') }}" class="big-text">勤務表へ</a>
    </div>

    <h2 style="text-align: center;">経費精算表</h2>

    <div id="user_info" style="text-align: center; margin-bottom: 20px;">
        {{-- 管理者が登録した詳細情報を優先的に参照 --}}
        社員番号：<input type="text" id="empId" value="{{ Auth::user()->detail->employee_code ?? '未設定' }}" readonly style="width: 80px; border:none; background:none; font-weight:bold;">
        名前：<input type="text" id="empName" value="{{ Auth::user()->detail->name_kanji ?? Auth::user()->name }}" readonly style="width: 150px; border:none; background:none; font-weight:bold;">
    </div>

    <table id="inputTable" style="margin: 0 auto; border: 2px solid black;">
        <tr><th>発生日</th><td><input id="occured_date" type="date" value="{{ date('Y-m-d') }}"></td></tr>
        <tr>
            <th>種別</th>
            <td>
                <select id="category" onchange="updateSubcategory()">
                    <option value="交通費">交通費</option>
                    <option value="宿泊費">宿泊費</option>
                    <option value="その他">その他物品等購入</option>
                </select>
                <select id="subcategory"></select>
            </td>
        </tr>
        <tr><th>出発地</th><td><input id="departure" type="text"></td></tr>
        <tr><th>目的地</th><td><input id="destination" type="text"></td></tr>
        <tr><th>支払先</th><td><input id="payee" type="text"></td></tr>
        <tr><th>金額</th><td><input id="amount" type="number" oninput="calc()"></td></tr>
        <tr><th>前払い額</th><td><input id="prepaid" type="number" oninput="calc()"></td></tr>
        <tr><th>請求額</th><td><span id="claimAmount">0</span></td></tr>
        <tr>
            <th>領収書</th>
            <td>
                <select id="receipt">
                    <option value="あり">あり</option>
                    <option value="なし">なし</option>
                </select>
            </td>
        </tr>
    </table>

    <div id="buttonBar" style="text-align: center; margin-top: 20px; display: flex; justify-content: center; gap: 10px;">
        <button id="submitBtn" onclick="submitData()">登録</button>
        <button id="saveCSV" onclick="exportCSV()">CSVファイルに保存</button>
        <button id="clearAll" onclick="clearData()">全てクリア</button>
        
        @if(Auth::user()->is_admin)
           <button type="button" class="btn-admin-panel" onclick="location.href='{{ route('expenses.admin') }}'">
                経費承認パネル
            </button>
        @endif
    </div>

    <h3 style="text-align: center; margin-top: 40px;">登録済データ（最新30件）</h3>
    <table id="dataTable" class="report-table">
        <thead>
            <tr style="background: #eee;">
                <th>申請</th><th>発生日</th><th>種別</th><th>詳細</th><th>出発地</th><th>目的地</th><th>支払先</th><th>金額</th><th>請求額</th><th>領収書</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $ex)
            <tr>
                <td>
                    @if($ex->status == 0 || $ex->status == 3)
                        <button class="status-btn btn-apply" onclick="updateStatus({{ $ex->id }}, 1)">申請</button>
                        <button class="status-btn btn-edit" onclick='editExpense(@json($ex))'>変更</button>
                        <button class="status-btn btn-delete" onclick="deleteExpense({{ $ex->id }})">削除</button>
                    @elseif($ex->status == 1)
                        <span class="pending">承認待ち</span>
                    @elseif($ex->status == 2)
                        <span class="approved">承認済み</span>
                    @endif
                </td>
                <td>{{ $ex->occured_date }}</td>
                <td>{{ $ex->category }}</td>
                <td>{{ $ex->subcategory }}</td>
                <td>{{ $ex->departure }}</td>
                <td>{{ $ex->destination }}</td>
                <td>{{ $ex->payee }}</td>
                <td class="text-right">{{ number_format($ex->amount) }}</td>
                <td class="text-right">{{ number_format($ex->claim_amount) }}</td>
                <td>{{ $ex->receipt }}</td>
            </tr>
            @if($ex->status == 3)
            <tr class="reject-reason-row">
                <td colspan="10" class="reject-reason-content">
                    <span class="rejected-text">【却下】</span> 理由：{{ $ex->reject_reason }}
                </td>
            </tr>
            @endif
            @empty
            <tr><td colspan="10">登録されたデータはありません。</td></tr>
            @endforelse
        </tbody>
    </table>

    <script>
        const options = {
            "交通費": ["鉄道", "バス", "タクシー", "ガソリン代"],
            "宿泊費": ["宿泊費"],
            "その他": ["レンタカー代", "物品購入"]
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
                alert('登録しました。');
                location.reload();
            }
        }

        async function updateStatus(id, status) {
            if (!confirm('管理者に申請しますか？')) return;

            try {
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
                    alert('申請を完了しました。管理者の承認をお待ちください。');
                    location.reload();
                } else {
                    const errorData = await res.json();
                    alert('エラーが発生しました：' + (errorData.message || '通信失敗'));
                }
            } catch (error) {
                console.error(error);
                alert('通信に失敗しました。');
            }
        }

        async function deleteExpense(id) {
            if (!confirm('本当に削除しますか？')) return;
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
            window.scrollTo(0, 0);
            alert('修正したい内容を入力し、再度「登録」ボタンを押してください。');
        }

        function clearData() {
            if(confirm("入力をクリアしますか？")) location.reload();
        }

        window.onload = updateSubcategory;
    </script>
</body>
</html>