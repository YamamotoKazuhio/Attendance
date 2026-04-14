<section>
    <h2 id="users">5. 管理者および一般ユーザー用ページの作成</h2>
    <h3 id="Profile">5-1. プロフィール（ユーザー詳細情報）</h3>
    <P>プロフィールの詳細表示・編集画面（user_details/show.blade.php）を、x-app-layout を使わずに、</br>
        管理者と一般社員の両方が利用できる統一デザインで作成する方法をまとめます。</br>
        この画面は、社員番号、入社日、住所、電話番号などの重要な個人情報を管理する場所です。
    </P>
    <ol>
        <li><strong>ビューファイルの作成</strong></br>
            <p tyle="margin-bottom: 5px;">
                以下の内容を新しいデザインとして保存します。</br>
                保存パス: resources/views/user_details/show.blade.php</br>
                <span onclick="openCodeModal('codeBlockShow')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    コード
                </span>
            </p>

            <div id="codeBlockShow" style="display: none;">
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; text-align: left;"><code>@php
            $path = base_path('resources/views/user_details/show.blade.php');
            if (file_exists($path)) {
                echo e(file_get_contents($path));
            } else {
                echo "ファイルが見つかりません: " . $path;
            }
        @endphp</code></pre>
            </div>
        </li>

        <li><strong>ルーティングの定義 (routes/web.php)</strong></br>
            <p tyle="margin-bottom: 5px;"> 
                UserDetailController の show と update メソッドに紐付けます。
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>    
            <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0;  margin-top: 0;"><code>Route::middleware(['auth'])->group(function () {
    // ユーザー詳細情報の表示
    Route::get('/user-details/{user}', [UserDetailController::class, 'show'])->name('user_details.show');
    
    // 更新処理
    Route::patch('/user-details/{user}', [UserDetailController::class, 'update'])->name('user_details.update');
});</code></pre>
        </li>

        <li><strong>反映の手順</strong>
            <ol>
                <li><strong>ファイルの配置:</strong>ローカルで作成したファイルを AWS（パブリック IPv4 アドレス）の /var/www/html/Attendance/resources/views/user_details/show.blade.php に配置してください。</li>
                <li><strong>コントローラーの挙動: </strong>UserDetailController.php 内で、管理者が他人の情報を編集する場合と、一般社員が自分の情報を編集する場合の両方を正しくハンドルしているか確認してください（すでにご提示いただいたコードで対応済みです）。</li>
                <li><strong>スタイルのポイント: </strong>「社員番号」や「入社日」など、一般社員が勝手に書き換えてはいけない項目には disabled 属性を付与し、管理者だけが編集できるようにしています。</li>
            </ol>

        </li>
    </ol>

    <h3 id="work-schedule">5-2. 勤務表</h3>
    <p>勤務表システム（Attendance Management System）を構築するために必要なファイル一式と、その構築手順をまとめました。</p>
    <ol>
        <li><strong>データベース設計図 (Migration)</strong>
            <p>
                データを保存するテーブルを作成します。</br>
                （既に attendances テーブルがあるのであれば、本作業は不要です。）</br>
                ファイル名: database/migrations/xxxx_xx_xx_create_attendances_table.php
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0"><code>public function up()
{
    Schema::create('attendances', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ユーザーID
        $table->date('work_date');           // 勤務日
        $table->string('attendance_type');    // 勤怠種別（出勤、有給など）
        $table->string('workplace')->nullable(); // 勤務先
        $table->string('shift')->nullable();     // シフト
        $table->time('start_time')->nullable();  // 開始時間
        $table->time('end_time')->nullable();    // 終了時間
        $table->decimal('break_time', 4, 2)->default(0); // 休憩時間
        $table->decimal('overtime', 4, 2)->default(0);   // 残業時間
        $table->decimal('work_time', 4, 2)->default(0);  // 稼働時間
        $table->integer('is_locked')->default(0);        // 0:未, 1:確定, 2:承認済
        $table->timestamps();

        // 同じユーザーが同じ日に2件登録できないように制約
        $table->unique(['user_id', 'work_date']);
    });
}</code></pre>
        </li>

        <li><strong>データの窓口 (Model)</strong>
            <p>プログラムからテーブルを操作するための設定です。</br>
                ファイル名: app/Models/Attendance.php
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0"><code>&lt;?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected &#36;fillable = [
        'user_id', 'work_date', 'attendance_type', 'workplace', 
        'shift', 'start_time', 'end_time', 'break_time', 
        'overtime', 'work_time', 'is_locked'
    ];

    public function user() {
        return &#36;this-&gt;belongsTo(User::class);
    }
}</code></pre>
        </li>

        <li><strong>司令塔 (Controller)</strong>
            <p>
                画面表示と保存のロジックを担当します。</br>
                ファイル名: app/Http/Controllers/AttendanceController.php
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; max-height: 300px; overflow-y: auto;"><code>&lt;php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 勤務表の表示
    public function index(Request $request)
    {
        $targetUser = Auth::user();
        $targetMonth = $request->input('month', Carbon::now()->format('Y-m'));
        
        // 指定月のデータを取得
        $attendances = Attendance::where('user_id', $targetUser->id)
            ->where('work_date', 'like', "$targetMonth%")
            ->get();

        return view('attendance', compact('attendances', 'targetUser', 'targetMonth'));
    }

    // 基本情報（所属・通勤費）の更新
    public function updateBasicInfo(Request $request)
    {
        $user = User::find($request->user_id);
        $user->department = $request->department;
        $user->daily_commute_fee = $request->commute_fee;
        $user->save();

        return response()->json(['message' => '基本情報を保存しました']);
    }
}</code></pre>
        </li>

        <li><strong>URLの設定 (Routes)</strong>
            <p>ブラウザのアドレスとコントローラーを紐付けます。</br>
                ファイル名: routes/web.php
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>use App\Http\Controllers\AttendanceController;

Route::middleware(['auth'])->group(function () {
    // 勤務表メイン
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    
    // 基本情報の非同期保存 (JSから呼ばれる)
    Route::post('/attendance/update-basic', [AttendanceController::class, 'updateBasicInfo'])
        ->name('attendance.updateBasicInfo');
});</code></pre>
        </li>

        <li><strong>Viewファイルの作成</strong>
            <p>
                以下の内容を新しいデザインとして保存します。</br>
                x-app-layout を使わず、独自のスタイルで構築します。</br>
                保存パス：resources/views/attendance.blade.php</br>
                <span onclick="openCodeModal('codeBlock_attendance')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    コード
                </span>
            </p>

            <div id="codeBlock_attendance" style="display: none;">
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; text-align: left;"><code>@php
            $path = base_path('/resources/views/attendance.blade.php');
            if (file_exists($path)) {
                echo e(file_get_contents($path));
            } else {
                echo "ファイルが見つかりません: " . $path;
            }
        @endphp</code></pre>
            </div>

        </li>

        <li><strong>.JavaScriptファイル</strong>
            <p>
                カレンダーの動的生成や計算ロジックを記述したファイルです。</br>
                保存パス：Public/js/Attendance.js</br>
                <span onclick="openCodeModal('codeBlock_JS_Attendance')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    JavaScript
                </span>
            </p>
            <div id="codeBlock_JS_Attendance" style="display: none;">
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; text-align: left;"><code>@php
            $path = base_path('Public/js/Attendance.js');
            if (file_exists($path)) {
                echo e(file_get_contents($path));
            } else {
                echo "ファイルが見つかりません: " . $path;
            }
        @endphp</code></pre>
            </div>
        </li>

        <li><strong>CSSファイル</strong>
            <p>
                保存パス：public/css/Attendance.css</br>
                <span onclick="openCodeModal('codeBlock_JS_Attendance_CSS')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    CSS
                </span>
            </p>
            <div id="codeBlock_JS_Attendance_CSS" style="display: none;">
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; text-align: left;"><code>@php
            $path = base_path('public/css/Attendance.css');
            if (file_exists($path)) {
                echo e(file_get_contents($path));
            } else {
                echo "ファイルが見つかりません: " . $path;
            }
        @endphp</code></pre>
            </div>
        </li>

        <li><strong>構築の順番（ロードマップ）</strong>
            <ol>
                <li><strong>Migrationを実行:</strong>php artisan migrate でDBテーブルを作成。</li>
                <li><strong>Modelを作成: </strong>Attendance.php を作成。</li>
                <li><strong>Controllerを作成: </strong> AttendanceController.php にロジックを記述。</li>
                <li><strong>Routesを登録:</strong>web.php にURLを定義。</li>
                <li><strong>Viewと静的ファイルを配置:</strong>web.php にURLを定義。</li>
            </ol>
            <p>この手順で進めることで、フロントエンド（JS）で計算されたデータが、バックエンド（Controller）を通じてDBへ保存される一連の流れが完成します。</p>
        </li>
    </ol>

    <h3 id="Expense-reimbursement-management">5-3. 経費精算管理</h3>
    <p>経費精算システムを構築するために必要なファイル一式と、それぞれの役割、具体的なコード内容をまとめました。</br>
        このシステムは、ユーザーが経費を入力し、DBに保存し、管理者がそれを承認・却下できる構成になっています。
    </p>
    <ol>
        <li><strong>データベース設計図 (Migration)</strong>
            <p>
                経費データを保存するテーブルを作成します。</br>
                ファイル名: database/migrations/xxxx_xx_xx_create_expenses_table.php
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0"><code>public function up()
{
    Schema::create('expenses', function (Blueprint $table) {
        $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 申請者ID
            $table->date('occured_date');          // 発生日
            $table->string('category');            // 種別（交通費等）
            $table->string('subcategory');         // 詳細（鉄道等）
            $table->string('departure')->nullable(); // 出発地
            $table->string('destination')->nullable(); // 目的地
            $table->string('payee')->nullable();     // 支払先
            $table->integer('amount');             // 金額
            $table->integer('prepaid')->default(0);  // 前払い額
            $table->integer('claim_amount');       // 請求額（金額 - 前払い）
            $table->string('receipt');             // 領収書の有無
            $table->integer('status')->default(0);   // 0:作成中, 1:申請中, 2:承認済, 3:却下
            $table->text('reject_reason')->nullable(); // 却下理由
            $table->timestamps();
            });
}</code></pre>
        </li>

        <li><strong>データの窓口 (Model)</strong>
            <p>
                プログラムから expenses テーブルを操作するための設定です。</br>
                ファイル名: app/Models/Expense.php
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0;"><code>&lt;?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'user_id', 'occured_date', 'category', 'subcategory', 'departure',
        'destination', 'payee', 'amount', 'prepaid', 'claim_amount',
        'receipt', 'status', 'reject_reason'
    ];

    // 経費データから見たユーザー（所属先）の定義
    public function user(): BelongsTo
    &#123;
        return $this->belongsTo(User::class);
    &#125;
}</code></pre>
        </li>

        <li><strong>司令塔 (Controller)</strong>
            <p>
                画面の表示、データの登録、ステータスの更新（申請・承認）を処理します。</br>
                保存パス:app/Http/Controllers/ExpenseController.php
            </p>
                <span onclick="openCodeModal('codeBlock_ExpenseController')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    PHP
                </span>

            <div id="codeBlock_ExpenseController" style="display: none;">
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; text-align: left;"><code>@php
            $path = base_path('app/Http/Controllers/ExpenseController.php');
            if (file_exists($path)) {
                echo e(file_get_contents($path));
            } else {
                echo "ファイルが見つかりません: " . $path;
            }
        @endphp</code></pre>
            </div>
        </li>

        <li>
            <strong>URLの設定 (Routes)</strong><br>
            <p>
                ブラウザのアドレスとコントローラーを紐付けます。<br>
                保存パス: routes/web.php
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5; margin-top: 0;"><code>@verbatim Route::middleware(['auth', 'verified'])->group(function () {
    // 4. 経費精算 (Expenses)
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::patch('/expenses/{expense}/status', [ExpenseController::class, 'updateStatus'])->name('expenses.status');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
});@endverbatim</code></pre>

            <p style="margin-top: 20px;">
                管理者がアクセスできるよう、<code>can:admin</code> ゲート内にルートを定義します。
            </p>

            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5;"><code>@verbatim Route::middleware(['auth', 'can:admin'])->group(function () {
    // 経費精算：承認待ち一覧
    Route::get('/expenses/admin', [ExpenseController::class, 'adminIndex'])->name('expenses.admin');
    
    // 承認・却下実行（ステータス更新）
    Route::patch('/expenses/{expense}/status', [ExpenseController::class, 'updateStatus'])->name('expenses.status');
});@endverbatim</code></pre>
        </li>

        <li><strong>View ファイルの作成</strong>
            <p>
                以下の内容を新しいデザインとして保存します。</br>
                x-app-layout を使わず、独自のスタイルで構築します。</br>
                保存パス:resources/views/expenses.blade.php</br>
                <span onclick="openCodeModal('codeBlock_expenses')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    HTML
                </span>
            </p>
            <div id="codeBlock_expenses" style="display: none;">
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; text-align: left;"><code>@php
            $path = base_path('resources/views/expenses.blade.php');
            if (file_exists($path)) {
                echo e(file_get_contents($path));
            } else {
                echo "ファイルが見つかりません: " . $path;
            }
        @endphp</code></pre>
            </div>
        </li>

        <li><strong>CSSファイル</strong>
            <p>保存パス：public/css/expenses.css</br>
                <span onclick="openCodeModal('codeBlock_expenses_css')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    CSS
                </span>
            </p>
            <div id="codeBlock_expenses_css" style="display: none;">
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; text-align: left;"><code>@php
            $path = base_path('public/css/expenses.css');
            if (file_exists($path)) {
                echo e(file_get_contents($path));
            } else {
                echo "ファイルが見つかりません: " . $path;
            }
        @endphp</code></pre>
            </div>
        </li>

        <li><strong>反映と運用のヒント</strong>
            <ol>
                <li><strong>アップロード: </strong>ローカルで作成したこのファイルを AWS（パブリック IPv4 アドレス）の /var/www/html/Attendance/resources/views/admin/expenses/index.blade.php に上書きしてください。</li>
                <li><strong>領収書の確認: </strong>領収書（画像）が正しく表示されない場合は、php artisan storage:link が実行されているか、または AWS 上の storage/app/public のシンボリックリンクが正しいかを確認してください。</li>
                <li><strong>デザインの統一: </strong> 金額の「右寄せ（text-align: right）」とフォント（等幅フォント）を適用することで、経理担当者が数値を読み取りやすいように配慮しています。</li>
            </ol>
            <p>これで、管理者メニューからスムーズに経費の承認作業が行えるようになります。</p>
        </li>

        <li><strong>構築のロードマップ</strong>
            <ol>
                <li><strong>Migration: </strong>php artisan migrate でテーブル作成。</li>
                <li><strong>Model: </strong>Expense.php を作成してリレーションと書き込み許可を設定。</li>
                <li><strong>Controller: </strong>ExpenseController.php で表示・保存・更新ロジックを記述。</li>
                <li><strong>Routes: </strong>web.php で各機能にURLを割り当て。</li>
                <li><strong>View: </strong>既存の expenses.blade.php を配置し、フォームの fetch 送信先が route('expenses.store') などと一致しているか確認。</li>
            </ol>
            <p>これで、ブラウザから入力した経費がデータベースに保存され、一覧に反映される一連のシステムが完成します。</p>
        </li>
    </ol>

    <h3 id="Operation-guide-work-schedule">5-4. 操作ガイド（勤務表）</h3>
    <p>操作ガイド（guide.blade.php）は、一般社員がシステムの使いかたに迷わないよう、図解やステップ形式で解説するページです。これも x-app-layout を使わずに、読みやすさを重視した独自デザインで作成します。</p>
    <ol>
        <li>ビューファイルの作成</br>
            <p>以下の内容を新しいデザインとして保存します。</br>
                保存パス:resources/views/guide.blade.php</br>
                <span onclick="openCodeModal('codeBlock_guide_views')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    HTML
                </span>
            </p>
            <div id="codeBlock_guide_views" style="display: none;">    
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; text-align: left;"><code>@php
            $path = base_path('resources/views/guide.blade.php');
            if (file_exists($path)) {
                echo e(file_get_contents($path));
            } else {
                echo "ファイルが見つかりません: " . $path;
            }
        @endphp</code></pre>
            </div>
        </li>

        <li><strong>ルーティングの設定 (routes/web.php)</strong>
            <p>一般ユーザーがログイン後にアクセスできるよう設定します。</p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5; margin-top: 0;"><code>{!! e(<<<'EOT'
Route::middleware(['auth'])->group(function () {
    // 操作ガイドの表示
    Route::get('/guide', function () {
        return view('guide');
    })->name('guide.show');
});
EOT
) !!}</code></pre>

        <li><strong>マニュアルPDFへの導線（オプション）</strong>
            <p>
                以前作成した「PDFマニュアル表示機能」と組み合わせるのも効果的です。</br>
                ガイドページの冒頭に以下のリンクを置くと、より詳細な情報を補足できます。</br>
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">HTML</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5;"><code>{!! e(<<<'EOT'
<div style="text-align: right; margin-bottom: 20px;">
    <a href="{{ route('manual.show') }}" target="_blank" style="color: #d93025; font-weight: bold; text-decoration: none;">
        📑 詳細なPDFマニュアルをダウンロード
    </a>
</div>
EOT
) !!}</code></pre>
        </li>
        <li>反映と運用のコツ
            <ol>
                <li><strong>アップロード: </strong>ローカルで作成した guide.blade.php を AWS（パブリック IPv4 アドレス）の /var/www/html/Attendance/resources/views/ へアップロードします。</li>
                <li><strong>ダッシュボードへのリンク設置: </strong>dashboard.blade.php の目立つ場所に「使いかたに困ったら：操作ガイドを見る」というリンクを追加してください。</li>
                <li><strong>図解の追加: </strong>実際の画面キャプチャを撮影し、public/images/guide/ フォルダに保存して、<img src="{{ asset('images/guide/sample.png') }}"> のように表示させると、より親切なガイドになります。</li>
            </ol>
            <p>これで、電話や口頭での説明を減らし、社員が自律的にシステムを使える環境が整います。</p>
        </li>
    </ol>

    <h3 id="Operation-guide-expense-reimbursement">5-5. 操作ガイド（経費精算）</h3>
    <p>guide_expense.blade.php を正しく機能させるための構築手順を、保存場所・ルーティング・リンク設置の3ステップで改めて整理しました。</p>

    <ol>
        <li><strong>Viewファイルの作成（表示内容）</strong>
            <p>
                まず、ブラウザに表示される「中身」を作成します。</br>
                保存パス:resources/views/guide_expense.blade.php</br>
                <span onclick="openCodeModal('codeBlock_guide')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    HTML
                </span>
            </p>
            <div id="codeBlock_guide" style="display: none;">    
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; text-align: left;"><code>@php
            $path = base_path('resources/views/guide_expense.blade.php');
            if (file_exists($path)) {
                echo e(file_get_contents($path));
            } else {
                echo "ファイルが見つかりません: " . $path;
            }
        @endphp</code></pre>
            </div>
        </li>

        <li><strong>ルーティングの追加（住所の設定）</strong>
            <p>
                編集ファイル: routes/web.php</p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5; margin-top: 0;"><code>{!! e(<<<'EOT'
// 経費精算ガイド用のルート
Route::get('/guide_expense', function () {
    return view('guide_expense'); // resources/views/guide_expense.blade.php を呼ぶ
})->name('guide_expense.show');
EOT
) !!}</code></pre>
        </li>

        <li><strong>各画面へのリンク設置（入り口の作成）</strong>
            <p>ユーザーが迷わないよう、主要な画面にガイドへのボタンを設置します。</p>
            <ol>
                <li>
                    <p>
                        サインアウトボタンの横に設置します。</br>
                    </p>
                    <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
                    <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5; margin-top: 0;"><code>{!! e(<<<'EOT'
<div class="top-left" style="display: flex; align-items: center;">
    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
        @csrf
        <button type="submit" class="big-text">サインアウト</button>
    </form>
    <a href="{{ route('guide_expense.show') }}" class="big-text" style="margin-left: 10px; text-decoration: none;">📖 ガイド</a>
</div>
EOT
) !!}</code>
            </pre>
                </li>

                <li>勤務表画面 (attendance.blade.php)</br>
                    <p>経費精算ボタンの横に設置します。</p>
                    <p style="margin-bottom: 5px; font-size: 1rem;">HTML</p>
                    </p>
                    <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5;"><code>{!! e(<<<'EOT'
<div class="header-left">
    <a href="{{ route('user_details.show', Auth::id()) }}" class="btn-header btn-profile">👤 プロフィール</a>
    <a href="{{ route('expenses.index') }}" class="btn-header btn-expense">💰 経費精算</a>
    <a href="{{ route('guide_expense.show') }}" class="btn-header" style="background-color: #6c757d; margin-left: 5px;">📖 操作ガイド</a>
</div>
EOT
) !!}</code></pre>
                </li>

                <li><strong>チェックリスト（動かない場合）</strong>
                    <p>構築後、もしエラーが出る場合は以下を確認してください。</p>
                    <ol>
                        <li><strong>1. ファイル名のスペル: </strong>resources/views/ の中にあるファイル名が guide_expense.blade.php と一文字違わず一致しているか。</li>
                        <li><strong>2. キャッシュのクリア: </strong>変更が反映されない場合、ターミナルで php artisan view:clear を実行してください。</li>
                        <li><strong>3. ルート名の不一致: </strong> web.php で書いた ->name('...') の中身と、リンクの route('...') の中身が同じか。</li>
                    </ol>
                    <p>この手順通りに進めれば、どの画面からでもスムーズにガイドを参照できるようになります。</p>
                </li>
            </ol>
        </li>
    </ol>
</section>
