<section id="mysql_linux">
    <h2>5. Linux版MySQLのインストール</h2>
    <p>Windows版MySQLのインストールは、こちらへ。</p>
    <h3 id="mysql_linux-download">5-1. MySQLのダウンロードおよびインストール</h3>
    <ol>
        <li><strong>MySQLのダウンロードおよびインストール</strong></br>
            以下のコマンドを実行して、MySQLをダウンロードします。
            <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
                # MySQLをダウンロード</br>
                sudo dnf install -y https://dev.mysql.com/get/mysql80-community-release-el9-1.noarch.rpm</br>
                # パッケージをダウンロード<br>
                sudo rpm --import https://repo.mysql.com/RPM-GPG-KEY-mysql-2023</br>
                # MySQLをインストール<br>
                sudo dnf install -y mysql-community-server
            </p>
        </li>

        <li><strong>起動と初期パスワードの設定</strong></br>
            Windows ではインストール中にパスワードを決めますが、Linux 版は初回起動時に</br>仮パスワードが発行します。</br>
            <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
                # 起動</br>
                sudo systemctl start mysqld</br>
                </br>
                # 仮パスワードの表示</br>
                sudo grep 'temporary password' /var/log/mysqld.log</br>
            </p></br>
            ※画面に表示される root@localhost: の後の文字列がパスワードです。
            <div class="guide-figure">
                <img src="{{ asset('images/guide/install/image101.png') }}" alt="images/guide/install/image101.png">
            </div>
            上記の例では、50)SfPPo%lpUが該当します。メモしておいてください。
        </li>

        <li><strong>セキュリティ設定（パスワードの変更）</strong></br>
            1.以下のコマンドを実行すると、対話形式で設定が始まります。</br>
            <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
                sudo mysql_secure_installation
            </p></br>
            <div class="guide-figure">
                <img src="{{ asset('images/guide/install/image102.png') }}" alt="images/guide/install/image102.png">
            </div>
            2.[Enter password for user root:]と表示するので、先ほどの仮パスワードを入力します。</br>
            <div class="guide-figure">
                <img src="{{ asset('images/guide/install/image103.png') }}" alt="images/guide/install/image103.png">
            </div>
            3.新しいパスワードを設定します（英大文字・小文字・数字・記号が必要です）。</br>
              なお、MySQL 8.0 のパスワードポリシー（強度制限）に引っかかってしまいます。</br>
              デフォルトでは 「8文字以上、かつ英大文字・小文字・数字・記号のすべてを含む」 必要があります。</br>
            <div class="guide-figure">
                <img src="{{ asset('images/guide/install/image104.png') }}" alt="images/guide/install/image104.png">
            </div>
            4.「Estimated strength of the password: 100Change the password for root ? ((Press y|Y for Yes, any other key for No) : 」と表示したならば、nを入力しEnterキーを押下します。</br>
            <div class="guide-figure">
                <img src="{{ asset('images/guide/install/image105.png') }}" alt="images/guide/install/image105.png">
            </div>
            5.その後の流れ</br>
            y を押してパスワードを確定させると、続けていくつか質問が出てきます。</br>
            基本的にはすべて y で答えて進めてください。</br>
            Remove anonymous users? (y/n) : y</br>
            誰でも入れる匿名ユーザーを削除しますか？（セキュリティのため削除推奨）</br>
            Disallow root login remotely? (y/n) : y</br>
            外部から root でログインできないようにしますか？（セキュリティのため禁止推奨）</br>
            Remove test database and access to it? (y/n) : y</br>
            テスト用のDBを削除しますか？</br>
            Reload privilege tables now? (y/n) : y</br>
            今すぐ設定を反映させますか？</br>
                        <div class="guide-figure">
                <img src="{{ asset('images/guide/install/image106.png') }}" alt="images/guide/install/image106.png">
            </div>
            最後に 「All done!」 と表示されれば、MySQL の初期セキュリティ設定は完了です。</br>
            <div class="guide-figure">
                <img src="{{ asset('images/guide/install/image107.png') }}" alt="images/guide/install/image107.png">
            </div>
        </li>
    </ol>
      
    <h3 id="mysql_linux-create">5-2. データベースの作成</h3>
    設定が終わったら、Laravelが接続するための「箱（データベース）」を一つ作ります。
    <ol>
        <li>
            DBにログインします。</br>
            <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
                mysql -u root -p
            </p>
        </li>

        <li>
            パスワードを入力します。</br>
            <div class="guide-figure">
                <img src="{{ asset('images/guide/install/image108.png') }}" alt="images/guide/install/image108.png">
            </div>
        </li>

        <li>
            テーブルを作成します。</br>
            <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
                CREATE DATABASE my-app
            </p>
        </li>

        <li>
            作成したテーブルを確認します。</br>
            <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
                show databases;
            </p>
            <div class="guide-figure">
                <img src="{{ asset('images/guide/install/image109.png') }}" alt="images/guide/install/image109.png">
            </div>
        </li>

        <li>
            終了します。</br>
            <p style="background-color: #2d2d2d; color: #f8f8f2; padding: 5px 15px; border-radius: 5px; display: inline-block; font-family: Consolas, monospace; margin: 10px 0;">
                show databases;
            </p>
            <div class="guide-figure">
                <img src="{{ asset('images/guide/install/image110.png') }}" alt="images/guide/install/image110.png">
            </div>
        </li>
    </ol>
</section>