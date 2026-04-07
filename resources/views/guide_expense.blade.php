<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>操作ガイド | 業務管理システム</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; line-height: 1.6; }
        .header { background: #1a73e8; color: white; padding: 20px; text-align: center; }
        .container { max-width: 900px; margin: 30px auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        
        h2 { border-left: 5px solid #1a73e8; padding-left: 15px; margin-top: 40px; color: #1a73e8; }
        .step { display: flex; margin-bottom: 30px; align-items: flex-start; }
        .step-number { background: #1a73e8; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 15px; flex-shrink: 0; }
        .step-content { flex-grow: 1; }
        .step-content strong { display: block; font-size: 1.1em; margin-bottom: 5px; }
        
        .info-box { background: #e8f0fe; border-radius: 8px; padding: 20px; margin: 20px 0; border-left: 5px solid #4285f4; }
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 0.9em; margin-top: 5px; }
        
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 0.9em; }
        th { background: #f2f2f2; }

        .footer-nav { text-align: center; margin-top: 50px; padding-bottom: 50px; }
        .btn-back { display: inline-block; background: #666; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; }
        
        .status-applied { color: #1976d2; font-weight: bold; }
        .status-approved { color: blue; font-weight: bold; }
        .status-rejected { color: red; font-weight: bold; }
    </style>
</head>
<body>

<div class="header">
    <h1>📖 システム操作ガイド</h1>
    <p>経費精算の申請・管理の手順</p>
</div>

<div class="container">
    <p>「経費精算表」では、業務で発生した交通費や宿泊費などの経費を登録し、管理者へオンラインで申請することができます。</p>

    <h2>1. 経費の登録手順</h2>
    
    <div class="step">
        <div class="step-number">1</div>
        <div class="step-content">
            <strong>情報の入力</strong>
            発生日、種別、支払先、金額などを入力します。
            <ul>
                <li><strong>種別：</strong> 「交通費」「宿泊費」「その他」を選択すると、右側の詳細項目が自動的に切り替わります。</li>
                <li><strong>請求額：</strong> 金額から前払い額を差し引いた金額が自動計算されます。</li>
            </ul>
        </div>
    </div>

    <div class="step">
        <div class="step-number">2</div>
        <div class="step-content">
            <strong>データの保存（登録）</strong>
            「登録」ボタンをクリックして、データを一時保存します。
            <br><small>※この段階ではまだ管理者に通知されません。下の一覧にデータが表示されることを確認してください。</small>
        </div>
    </div>

    <div class="step">
        <div class="step-number">3</div>
        <div class="step-content">
            <strong>管理者への申請</strong>
            一覧の「申請」列にある<span class="status-badge" style="background: #e3f2fd; color: #1976d2; border: 1px solid #2196f3;">申請</span>ボタンをクリックします。
            <br>確認メッセージで「OK」を押すと、管理者の承認待ち状態になります。
        </div>
    </div>

    <h2>2. ステータス（状態）の見方</h2>
    <p>一覧表の左側にある表示で、現在の申請状況を確認できます。</p>
    <table>
        <tr>
            <th>表示内容</th>
            <th>意味</th>
            <th>可能な操作</th>
        </tr>
        <tr>
            <td><span class="status-badge" style="background: #fff3e0; color: #e65100; border: 1px solid #ff9800;">変更</span> / <span class="status-badge" style="background: #ffebee; color: #c62828; border: 1px solid #f44336;">削除</span></td>
            <td>一時保存中 / 却下</td>
            <td>内容の修正、またはデータの削除が可能です。</td>
        </tr>
        <tr>
            <td><span class="status-applied">承認待ち</span></td>
            <td>申請完了</td>
            <td>管理者が確認中です。修正・削除はできません。</td>
        </tr>
        <tr>
            <td><span class="status-approved">承認済み</span></td>
            <td>精算確定</td>
            <td>管理者の承認が完了しました。修正・削除は一切できません。</td>
        </tr>
    </table>

    <div class="info-box" style="border-left-color: #d32f2f; background-color: #fff0f0;">
        <strong>🚩 却下された場合</strong><br>
        管理者が内容を「却下」した場合、一覧に赤字で<strong>却下理由</strong>が表示されます。
        「変更」ボタンを押して内容を修正し、再度「登録」→「申請」を行ってください。
    </div>

    <h2>3. 便利な機能</h2>
    <div class="step">
        <div class="step-number">★</div>
        <div class="step-content">
            <strong>CSVファイルに保存</strong>
            「CSVファイルに保存」ボタンを押すと、現在表示されているデータをExcel等で開けるCSV形式でダウンロードできます。
        </div>
    </div>
    <div class="step">
        <div class="step-number">★</div>
        <div class="step-content">
            <strong>クイック修正</strong>
            「変更」ボタンを押すと、入力フォームにそのデータの内容が自動でセットされます。内容を書き換えて再度「登録」を押すだけで修正が完了します。
        </div>
    </div>
</div>

<div class="footer-nav">
    <a href="{{ route('expenses.index') }}" class="btn-back">経費精算表へ戻る</a>
</div>

</body>
</html>