<section>
    <h2 id="implementation-flow">7. 実装フロー</h2>
    <h3 id="Overall-process-flow">7-1. 全体プロセスフロー</h3>
    <p>この図は、一般社員の日常業務から管理者の最終承認までの流れを示しています。</p>
    <img src="{{ asset('images/guide/install/image113.png') }}" alt="実装フロー図" style="max-width:100%; height:auto;">
    
    <h3 id="Detailed-sequence">7-2. 詳細シーケンス（勤怠・経費）</h3>
    <p>各エンティティ間のデータのやり取りを定義したフローです。</p>
    <h4>2.1 勤怠打刻・申請フロー</h4>
    <ol>
        <li>ユーザー: AttendanceController@store へ打刻データを送信。</li>
        <li>システム: work_date と user_id を確認。
            <ul>
                <li>既存データなし：create（新規作成）</li>
                <li>既存データあり：update（更新）</li>
            </ul>
        </li>
        <li>ユーザー: 月末に「確定ボタン」を押下。</li>
        <li>システム: is_locked を 1（申請中） に変更。</li>
    </ol>
    <h4>2.2 経費承認ワークフロー</h4>
    <ol>
        <li>ユーザー: 経費入力後「申請」を実行。</li>
        <li>システム: status を 1 に変更し、本人による Edit/Delete を不可にする。</li>
        <li>管理者: ExpenseController@adminIndex で申請一覧を確認。</li>
        <li>管理者: 承認または却下（却下時は reject_reason を保存）を実行。</li>
        <li>システム: status を 2（承認） または 3（却下） に更新。</li>
    </ol>

    <h3 id="Implementation-checkpoints">3. 実装上の重要チェックポイント（終了条件）</h3>
    <p>システムを「終了（クローズ）」状態にするためのロジックフローです。</p>
    <ul>
        <li>月次確定のトリガー:
            <ul>
                <li>管理者が admin/finalize エンドポイントを叩く。</li>
            </ul>
        </li>
        <li>ガード条件:
            <ul>
                <li>is_locked が 2 のレコードに対して store や update が呼ばれた場合、</br>
                    Middleware または Controller レベルで 403 Forbidden を返却する。</li>
            </ul>
        </li>
        <li>完了状態:
            <ul>
                <li>当該月の全ユーザーの is_locked が 2 となり、</br>
                    UI上の保存ボタンが非活性化（disabled）されている状態。</li>
            </ul>
        </li>
    </ul>

    <h3 id="File-structure">4. ファイル構成（再確認）</h3>
    <p>このフローを実現するために必要な主要ファイル群です。</p>
    <ul>    
        <li>Entry: top_page.blade.php, DashboardController.php</li>
        <li>Logic: AttendanceController.php, ExpenseController.php</li>
        <li>Security: AdminMiddleware.php, Auth ゲート</li>
        <li>View: attendance.blade.php, expenses.blade.php, admin/dashboard.blade.php</li>
    </ul>

</section>

