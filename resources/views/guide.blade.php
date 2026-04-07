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
    </style>
</head>
<body>

<div class="header">
    <h1>📖 システム操作ガイド</h1>
    <p>日々の勤怠入力・経費精算の進め方</p>
</div>

<div class="container">
    <p>このシステムでは、日々の「勤怠管理（勤務表）」と「経費精算」をウェブ上で行うことができます。</br>以下の手順に従って操作してください。</p>

    <h2>1. 勤怠管理（勤務表の入力）</h2>
    
    <div class="step">
        <div class="step-number">1</div>
        <div class="step-content">
            <strong>勤務表画面へ移動</strong>
            e-mailアドレスとパスワードを入力し、ログインします。ログインすると勤務表が表示されます。</br>画面上部には現在の「有給残日数」が表示されます。
        </div>
    </div>

    <div class="step">
        <div class="step-number">2</div>
        <div class="step-content">
            <strong>勤怠種別と時刻の入力</strong>
            各日付の「勤怠」列から「出勤」「現場出勤」「有給」などを選択し、開始・終了・休憩時間を入力します。
            <ul>
                <li><strong>有給・欠勤：</strong> 選択すると稼働時間が自動設定（8.00 / -8.00）されます。</li>
                <li><strong>自動計算：</strong> 入力された内容から残業時間と稼働合計がリアルタイムに算出されます。</li>
            </ul>
        </div>
    </div>

    <div class="step">
        <div class="step-number">3</div>
        <div class="step-content">
            <strong>基本情報の確定</strong>
            所属グループや通勤費（日額）を変更した場合は、情報欄の右にある「確定」ボタンを必ずクリックしてください。
        </div>
    </div>

    <div class="info-box">
        <strong>💡 ステータスの確認</strong><br>
        勤務表の上部には処理状況が表示されます。
        <ul>
            <li><span class="status-badge" style="background: #fff5f5; color: #d32f2f;">🚩 確定（承認待ち）</span>：入力が完了し、管理者の確認を待っている状態です。</li>
            <li><span class="status-badge" style="background: #e6ffed; color: #2e7d32;">✅ 承認済み</span>：管理者の承認が完了しました。この状態になると<strong>修正・編集は一切できません</strong>。</li>
        </ul>
    </div>

    <h2>2. 経費精算の申請</h2>
    <div class="step">
        <div class="step-number">1</div>
        <div class="step-content">
            <strong>申請画面へ移動</strong>
            ヘッダーの「経費精算」をクリックします。
        </div>
    </div>
    <div class="step">
        <div class="step-number">2</div>
        <div class="step-content">
            <strong>領収書の添付と送信</strong>
            日付、項目、金額を入力します。領収書がある場合には、別途ファイルを管理部まで送付してください。
        </div>
    </div>

    <h2>3. 通勤費の自動計算について</h2>
    <p>このシステムでは、以下の勤怠種別を選択した日数に応じて、設定された「通勤費（日額）」から合計金額を自動算出します。</p>
    <table>
        <tr>
            <th>対象となる種別</th>
            <td>出勤、現場出勤、通常勤務、振替出勤</td>
        </tr>
        <tr>
            <th>計算式</th>
            <td>対象日数 × 設定された通勤費日額 = 画面下部の「通勤費合計」</td>
        </tr>
    </table>

    <div class="info-box" style="border-left-color: #fbc02d; background-color: #fff9c4;">
        <strong>❓ 困ったときは</strong><br>
        ・<strong>承認済みデータの修正：</strong> 管理者へ依頼して「確定解除」をしてもらってください。<br>
        ・<strong>祝日の表示：</strong> カレンダーの背景が赤くなっている日は祝日です。内閣府のデータに基づき自動判別されています。
    </div>
</div>

<div class="footer-nav">
    <a href="{{ route('attendance.index') }}" class="btn-back">←勤務表へ戻る</a>
</div>

</body>
</html>