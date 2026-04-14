<section>
    <h2 id="requirements-definition">1.データベースのテーブルを作成</h2>
    <p>Laravelでデータベースのテーブルを作成・管理する際に使用するコマンドを、実行する順番に沿ってすべて書き出します。<br>
        基本的には、「設計図（マイグレーションファイル）を作るコマンド」と、</br>
        それを「データベースに反映（実行）するコマンド」の2段階に分かれます。
    </p>

    <h2 id="migration-file">1-1.設計図（マイグレーションファイル）を作成する</h2>
        <p>各機能のテーブルを作成するための「雛形ファイル」を生成します。</p>
        <p>Bash</br>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code># 勤怠管理テーブルの設計図を作成
php artisan make:migration create_attendance_table

# 経費精算テーブルの設計図を作成
php artisan make:migration create_expenses_table

# 社員詳細情報テーブルの設計図を作成
php artisan make:migration create_user_details_table</code></pre>
                <p>・実行後、database/migrations/ フォルダ内にファイルが作成されます。</p>

    <h2 id="run-migration">1-2.設計図をデータベースに反映する</h2>
        <p>作成した設計図の内容を、実際のMySQLなどのデータベースにテーブルとして作成します。</p>
        <p>Bash</br>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code># 未実行のマイグレーションをすべて実行（テーブル作成）
php artisan migrate</code></pre>
            </p>

    <h2 id="rollback">1-3.やり直し・状態確認に使うコマンド（重要）</h2>
        <p>開発中にカラム（列）を追加し忘れたり、型を間違えたりした際によく使います。</p>
        <table>
            <tr>
                <th>コマンド</th>
                <th>役割</th>
                </tr>
                <tr>
                    <td>php artisan migrate:status</td>
                    <td>どのファイルが実行済みか、未実行かを確認します。</td>
                </tr>
                <tr>
                    <td>php artisan migrate:rollback</td>
                    <td>直前のマイグレーション実行を取り消します（1回分戻る）。</td>
                </tr>
                <tr>
                    <td>php artisan migrate:reset</td>
                    <td>すべてのテーブルを削除して初期状態に戻します。</td>
                </tr>
                <tr>
                    <td>php artisan migrate:refresh</td>
                    <td>全テーブルを一度削除し、最初からすべて作り直します。</td>
                </tr>
                <tr>
                    <td>php artisan migrate:fresh</td>
                    <td>rollback処理を無視して全テーブルをドロップし、</br>作り直します（開発中最強）。</td>
                </tr>

            </table>

    <h2 id="time-saving">1-4.モデルと同時に作成する時短テクニック</h2>
        <p>「モデル（データの窓口）」と「マイグレーション（テーブル設計図）」を一度に作るコマンドが、実務では最もよく使われます。</p>
        <p>Bash</br>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code># モデル作成と同時にマイグレーションファイルも作成 (-m オプション)
php artisan make:model 名前 -m
php artisan make:model Expense -m
php artisan make:model UserDetail -m</code></pre>
        </p>

    <h2 id="caution">1-5.【注意】AWSなど本番環境での実行</h2>
        <p>AWS（EC2）などの本番環境でテーブルを作成する際は、誤操作防止の警告が出ることがあります。</p>
        <p>Bash</br>
           <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code># 強制的に実行する場合（警告を無視してテーブルを作成）
php artisan migrate --force</code></pre>
        </p>

    <h2 id="hints">1-6.実行のヒント</h2>
        <p>マイグレーションの実行に関する、ちょっとしたヒントをまとめます。</p>
        <ol>
            <li>まず php artisan make:model 名前 -m でファイルを作る。</li>
            <li>database/migrations/xxxx_create_... の中身（$table->string(...) など）をエディタで書き換える。</li>
            <li>php artisan migrate で反映する。</li>
        </ol>
        <p>この3ステップが基本のルールになります。</p>
</section>
