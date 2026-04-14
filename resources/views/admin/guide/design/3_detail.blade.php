<section>
    <h2 id="Detailed-design-document">6. 詳細設計書</h2>
    <p>このセクションでは、業務管理システムの詳細設計書を示します。</br>
        データベースの物理設計、アプリケーションロジック、API・ルーティング設計、UI/UXの詳細仕様、インフラ・デプロイ構成など、実装に必要な具体的な情報を網羅しています。
    </p> 
    <h3 id="database-physical-design">6-1. データベース物理設計</h3>
    <h4>6-1-1. users テーブル (認証・権限)</h4>
    <table>
        <thead>
            <tr>
                <th>カラム名</th>
                <th>データ型</th>
                <th>制約</th>
                <th>説明</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>id</td>
                <td>bigint</td>
                <td>PK, AutoInc</td>
                <td>ユーザー識別子</td>
            </tr>
            <tr>
                <td>email</td>
                <td>varchar(255)</td>
                <td>Unique, NotNull</td>
                <td>ログインID</td>
            </tr>
            <tr>
                <td>password</td>
                <td>varchar(255)</td>
                <td>NotNull</td>
                <td>ハッシュ化されたパスワード</td>
            </tr>
            <tr>
                <td>is_admin</td>
                <td>tinyint(1)</td>
                <td>Default: 0</td>
                <td>管理者フラグ (1: 管理者)</td>
            </tr>
        </tbody>
    </table>
    <h4>6-1-2. user_details テーブル (社員属性)</h4>
    <table>
        <thead>
            <tr>
                <th>カラム名</th>
                <th>データ型</th>
                <th>制約</th>
                <th>説明</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>id</td>
                <td>bigint</td>
                <td>PK, AutoInc</td>
                <td>ID</td>
            </tr>
            <tr>
                <td>user_id</td>
                <td>bigint</td>
                <td>FK, Unique</td>
                <td>users.id と紐付け</td>
            </tr>
            <tr>
                <td>employee_code</td>
                <td>varchar(20)</td>
                <td>Unique, NotNull</td>
                <td>社員番号</td>
            </tr>
            <tr>
                <td>name_kanji</td>
                <td>varchar(100)</td>
                <td>NotNull</td>
                <td>氏名（漢字）</td>
            </tr>
            <tr>
                <td>department</td>
                <td>varchar(100)</td>
                <td>-</td>
                <td>所属部署</td>
            </tr>
            <tr>
                <td>daily_commute_fee</td>
                <td>int</td>
                <td>Default: 0</td>
                <td>1日あたりの通勤費</td>
            </tr>

        </tbody>
    </table>
    <h4>6-1-3. attendances テーブル (勤務・休暇)</h4>
    <table>
        <thead>
            <tr>
                <th>カラム名</th>
                <th>データ型</th>
                <th>制約</th>
                <th>説明</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>id</td>
                <td>bigint</td>
                <td>PK, AutoInc</td>
                <td>ID</td>
            </tr>
            <tr>
                <td>user_id</td>
                <td>bigint</td>
                <td>FK, NotNull</td>
                <td>users.id と紐付け</td>
            </tr>
            <tr>
                <td>work_date</td>
                <td>date</td>
                <td>NotNull</td>
                <td>勤務日</td>
            </tr>
            <tr>
                <td>attendance_type</td>
                <td>varchar(20)</td>
                <td>NotNull</td>
                <td>出勤, 有給, 欠勤, 特休 など</td>
            </tr>
            <tr>
                <td>start_time</td>
                <td>time</td>
                <td>-</td>
                <td>始業時刻</td>
            </tr>
            <tr>
                <td>end_time</td>
                <td>time</td>
                <td>-</td>
                <td>終業時刻</td>
            </tr>
            <tr>
                <td>overtime</td>
                <td>decimal(4,2)</td>
                <td>Default: 0</td>
                <td>残業時間</td>
            </tr>
            <tr>
                <td>is_locked</td>
                <td>tinyint</td>
                <td>Default: 0</td>
                <td>0:作成中, 1:申請中, 2:承認済, 3:却下</td>
            </tr>
        </tbody> 
    </table>
    <h4>6-1-4. expenses テーブル (経費)</h4>
    <table>
        <thead>
            <tr>
                <th>カラム名</th>
                <th>データ型</th>
                <th>制約</th>
                <th>説明</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>id</td>
                <td>bigint</td>
                <td>PK, AutoInc</td>
                <td>ID</td>
            </tr>
            <tr>
                <td>user_id</td>
                <td>bigint</td>
                <td>FK, NotNull</td>
                <td>users.id と紐付け</td>
            </tr>
            <tr>
                <td>occured_date</td>
                <td>date</td>
                <td>NotNull</td>
                <td>発生日</td>
            </tr>
            <tr>
                <td>category</td>
                <td>varchar(50)</td>
                <td>NotNull</td>
                <td>交通費, 宿泊費, その他 などのカテゴリ</td>
            </tr>
            <tr>
                <td>amount</td>
                <td>int</td>
                <td>NotNull</td>
                <td>総額（税込）</td>
            </tr>
            <tr>
                <td>claim_amount</td>
                <td>int</td>
                <td>NotNull</td>
                <td>請求額（総額 - 前払い）</td>
            </tr>
            <tr>
                <td>status</td>
                <td>tinyint</td>
                <td>Default: 0</td>
                <td>0:下書き, 1:申請中, 2:承認済, 3:却下</td>
            </tr>
            <tr>
                <td>reject_reason</td>
                <td>text</td>
                <td>-</td>
                <td>却下時の理由</td>
            </tr>
        </tbody>
    </table>

<h2 id="Application-logic-design">6-2. アプリケーションロジック設計</h2>
<h3>6-2-1. 勤怠計算ロジック</h3>
<ul>
    <li>実労働時間の算出: (end_time - start_time) - 休憩時間</li>
    <li>残業時間の自動計算: 実労働時間が 8時間を超えた分を overtime カラムへ自動加算。</li>
    <li>月次確定ロジック: 対象年月の is_locked がすべて 2 (承認済) になった時点で、</br>
        その月の新規登録・編集・削除 API を 403 Forbidden で遮断する。</li>
</ul>
<h3>6-2-2. 経費申請ワークフロー</h3>
<ol>  
    <li>保存 (store): status = 0 でレコード作成。本人による編集・削除可。</li>
    <li>申請 (updateStatus): 本人がボタン押下により status = 1 へ変更。編集・削除不可へ。</li>
    <li>承認/却下 (admin updateStatus): 管理者が 2 (承認) または 3 (却下) へ変更。
        <ul>
            <li>却下時は reject_reason の入力をバリデーションで必須とする。</li>
        </ul>
    </li>
</ol>
        
<h2 id="API-and-Routing-Design">6-3. API・ルーティング設計 (主要エンドポイント)</h2>    
 <h3>6-3-1. 経費精算 (Expense API)</h3>
<ul>
    <li>POST /expenses: 経費の新規登録。amount - prepaid をサーバーサイドで再計算し claim_amount を格納。</li>
    <li>PATCH /expenses/{id}/status: ステータスの更新。
        <ul>
            <li>一般ユーザー：0 → 1 への変更のみ許可。</li>
            <li>管理者：1 → 2 または 1 → 3 への変更のみ許可。</li>
        </ul>
    </li>
</ul>
<h3>6-3-2. 勤怠管理 (Attendance API)</h3>
<ul>
    <li>POST /attendance/save: 打刻データの保存。</br>
        当日データが既にある場合は update、ない場合は create を行う（Upsert処理）。
    </li>
</ul>

<h2 id="UI-UX-Specifications">6-4. UI/UX 詳細仕様</h2>
<h3>6-4-1. 共通コンポーネント</h3>
<ul>
    <li>サイドナビ/ヘッダー: ログインユーザー名と権限に応じたメニュー（管理者パネル等）を表示。</li>
    <li>バリデーション表示: Laravel の ErrorBag をフロントエンド（React/Blade）で受け取り、<br>
        入力項目の直下に赤文字で表示。</li>
</ul>
<h3>6-4-2. 画面別制御</h3>
<ul>
    <li>経費精算画面:
        <ul>
            <li>status >= 1 のデータは編集・削除ボタンを非表示にし、テキストのみ表示。</li>
            <li>却下されたデータ（status = 3）は「編集ボタン」を再表示し、再申請を促す。</li>
        </ul>
    </li>
    <li>管理者ダッシュボード:
        <ul>
            <li>未処理（status = 1）の件数をカウントし、バッジで通知する。</li>
        </ul>
    </li>
</ul>

<h2 id="Infrastructure-deployment-configuration">6-4-5. インフラ・デプロイ構成</h2>
<h3>6-5-1. インフラ構成</h3>
<ul>
    <li>Webサーバー: AWS EC2 (Amazon Linux 2023 / Nginx)</li>
    <li>DBサーバー: AWS RDS (MySQL 8.0)</li>
</ul>
<h3>6-5-2. デプロイフロー</h3>
<ol>
    <li>GitHub へ Push。</li>
    <li>GitHub Actions が起動し、PHPUnit によるテスト実行。</li>
    <li>テスト通過後、AWS CodeDeploy を介して EC2 内部の /var/www/html を更新。</li>
    <li>php artisan migrate --force を実行し、DB スキーマを最新化。</li>
</ol>
</section>
