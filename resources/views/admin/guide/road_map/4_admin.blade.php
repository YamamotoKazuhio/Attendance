<section>
    <h2 id="admin">4. 管理者のみのページの作成</h2>
    <h3 id="administrator-only-menu">4-1. 管理者専用メニュー</h3>
    <p>管理者専用メニュー（Admin Dashboard）は、各管理機能（経費承認、勤務承認、名簿管理、月次集計）への入り口となる重要なハブです。</br>
        これを構築するために必要なファイル一式と、各機能へ安全にアクセスするための「管理者認証」を含めた手順をまとめました。</p>
    <ol>
        <li><strong>司令塔 (Controller)</strong>
            <p>
                ダッシュボードを表示するだけのシンプルなコントローラーですが、ここで「各機能の未処理件数」などを取得して表示させると利便性が高まります。</br>
                ファイル名: app/Http/Controllers/AdminController.php
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; max-height: 300px; overflow-y: auto;"><code>&lt;?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // 統計情報の取得（ダッシュボードに表示用）
        $pendingExpenses = Expense::where('status', 1)->count();     // 経費申請待ち数
        $pendingAttendances = Attendance::where('is_locked', 1)->count(); // 勤務確定待ち数

        return view('admin.dashboard', compact('pendingExpenses', 'pendingAttendances'));
    }
}
</code></pre>
        </li>

        <li><strong>アクセス制限 (Middleware)</strong>
            <p>
                一般社員が管理者メニューにURLを直接入力して入ってこられないようガードをかけます。</br>
                ファイル名: app/Http/Middleware/AdminMiddleware.php</br>
                ※ php artisan make:middleware AdminMiddleware で作成<br>
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0;"><code>&lt;?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // ログインしており、かつ is_admin フラグが 1 の場合のみ許可
        if (Auth::check() && Auth::user()->is_admin == 1) {
            return $next($request);
        }

        // 権限がない場合はホームへ飛ばす
        return redirect('/attendance')->with('error', '管理者権限が必要です。');
    }
}
</code></pre>
            <p>
                ※作成後、bootstrap/app.php（Laravel 11）または Kernel.php（Laravel 10以前）で、</br>
                このミドルウェアを admin という名前で登録してください。
            </p>

        </li>

        <li><strong>URLの設定 (Routes)</strong>
            <p>
                管理者機能を一括でグループ化し、先ほどのミドルウェアで保護します。</br>
                ファイル名: routes/web.php
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0;"><code>use App\Http\Controllers\AdminController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\UserDetailController;

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // 管理者トップ（ダッシュボード）
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // 1. 経費精算承認
    Route::get('/expenses', [ExpenseController::class, 'adminIndex'])->name('expenses.admin');
    
    // 2. 休暇・残業承認
    Route::get('/attendance/approve', [AttendanceController::class, 'adminApproveIndex'])->name('admin.attendance.approve');
    
    // 3. 社員名簿管理
    Route::get('/users', [UserDetailController::class, 'index'])->name('admin.users.index');
    
    // 4. 月次集計・確定
    Route::get('/monthly-report', [AttendanceController::class, 'monthlyReport'])->name('admin.monthly_report');
});
</code></pre>
            </P>
        </li>

        <li><strong>管理者専用メニュー画面 (View)</strong>
            <p>
                各機能へアクセスするためのパネル（カード型デザイン）を作成します。</br>
                x-app-layout を使わず、独自のスタイルを適用して「勤怠管理」や「ユーザー管理」へのボタンを配置します。</br>
                ファイル名:<cord>resources/views/admin/selector.blade.php</cord></br>
                <span onclick="openCodeModal('codeBlock_dashboard')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    HTML
                </span>
            </p>
            <div id="codeBlock_dashboard" style="display: none;">
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; text-align: left;"><code>@php
                $path = base_path('resources/views/admin/selector.blade.php');
                if (file_exists($path)) {
                    echo e(file_get_contents($path));
                } else {
                    echo "ファイルが見つかりません: " . $path;
                }
                @endphp</code></pre>
            </div>
        </li>

        <li><strong>構築のロードマップ</strong>
            <ol>
                <li><strong>Middlewareの作成：</strong>管理者以外を弾く「門番」を設置します。</li>
                <li><strong>Routesの整理：</strong>すべての管理者用ルートを AdminMiddleware のグループ内に入れます。</li>
                <li><strong>Controllerの実装：</strong>未処理件数などを計算するロジックを書きます。</li>
                <li><strong>Viewの作成：</strong>4つの大きなボタンが並ぶダッシュボード画面を作成します。</li>
                <li><strong>導線の設置：</strong>attendance.blade.php や expenses.blade.php に、</br>
                    管理者のみ表示される [管理者メニュー] というリンクを追加します。
                </li>
            </ol>
            <p>
                このメニューがあることで、管理者はバラバラのURLを覚える必要がなくなり、</br>
                一つの画面からすべての管理業務を効率的に行えるようになります。</br>
                </br>
                管理者専用メニュー（admin/menu）を、標準レイアウト（Dashboardのロゴなど）を使わずに、</br>
                シンプルで使いやすい独自デザインで作成・修正する方法をまとめます。</br>
                </br>
                他の管理者画面への「戻るボタン」の設置</br>
                「月次集計」や「社員一覧」の画面から、このメニューに戻れるようにリンクを貼ると</br>
                運用がスムーズになります。</br>
                各画面（admin/users/index.blade.php など）のヘッダー付近に以下を追加します。
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">HTML</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;a href="&#123;&#123; route('admin.menu') &#125;&#125;" style="text-decoration: none; color: #1a73e8;"&gt;← 管理者メニューへ戻る&lt;/a&gt;</code></pre>
            
        </li>
    </ol>
    </p>

    <h3 id="employee-roster-management">4-2. 社員名簿管理</h3>
    <p>
        社員名簿管理（User Details Management）は、システムの基盤となるユーザー情報（社員番号、氏名、入社日、権限など）を管理する重要な機能です。</br>
        Laravelの標準的な認証機能（BreezeやJetstreamなど）に加え、実務に必要な詳細情報を管理するための「UserDetail」モデルを分離して構築する手順をまとめました。
    </p>
    <ol>
        <li><strong>データベース設計図 (Migration)></strong>
            <p>
                標準の users テーブルとは別に、社員固有の情報を保存する user_details テーブルを作成します。</br>
                ファイル名: database/migrations/xxxx_xx_xx_create_user_details_table.php
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0;"><code>public function up()
{
    Schema::create('user_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade'); // ユーザーID
        $table->string('employee_code')->unique(); // 社員番号
        $table->string('name_kanji');              // 氏名（漢字）
        $table->string('name_kana')->nullable();   // 氏名（カナ）
        $table->string('department')->nullable();  // 部署
        $table->date('hire_date')->nullable();     // 入社日
        $table->date('birth_date')->nullable();    // 生年月日
        $table->integer('daily_commute_fee')->default(0); // 1日あたりの通勤費
        $table->timestamps();
    });
}</code></pre>
        </li>

        <li><strong>データの窓口 (Model)</strong>
            <p>
                UserモデルとUserDetailモデルの紐付け（1対1のリレーション）を設定します。</br>
                ファイル名: app/Models/UserDetail.php
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0;"><code>&lt;?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $fillable = [
        'user_id', 'employee_code', 'name_kanji', 'name_kana', 
        'department', 'hire_date', 'birth_date', 'daily_commute_fee'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}</code></pre>
            <p>ファイル名: app/Models/User.php (既存ファイルに追記)</p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0;"><code>public function detail() {
    return $this->hasOne(UserDetail::class);
}</code></pre>
        </li>

        <li><strong>司令塔 (Controller)</strong>
            <p>
                名簿の一覧表示、新規登録、編集、削除を処理します。</br>
                ファイル名:<cord>app/Http/Controllers/UserDetailController.php</cord></br>
                <span onclick="openCodeModal('codeBlock_UserDetailController')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    PHP
                </span>
            </p>

            <div id="codeBlock_UserDetailController" style="display: none;">
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; text-align: left;"><code>@php
            $path = base_path('app/Http/Controllers/UserDetailController.php');
            if (file_exists($path)) {
                echo e(file_get_contents($path));
            } else {
                echo "ファイルが見つかりません: " . $path;
            }
        @endphp</code></pre>
            </div>
        </li>

        <li><strong>構築のロードマップ</strong>
            <ol>
                <li><strong>Migration：</strong>テーブルを作成。</li>
                <li><strong>Model：</strong>User と UserDetail のリレーションを設定。</li>
                <li><strong>Controller：</strong>UserDetailController を作成し、CRUD（一覧・編集・更新）を実装。</li>
                <li><strong>Route：</strong>管理者専用ミドルウェアで保護されたルートを追加。</li>
                <li><strong>View：</strong>既存のシステムデザインに合わせた管理画面を作成。</li>
            </ol>
            <p>
                この構成にすることで、ログイン用の users テーブルはシンプルに保ちつつ、</br>
                勤怠計算や給与計算に必要な「通勤費」や「社員番号」などの実務データを</br>
                user_details で柔軟に管理できるようになります。</br>
                </br>
                x-app-layout を使用せず、他の管理画面（月次集計や管理者メニュー）とデザインを統一した、</br>
                独自の社員名簿管理画面（admin/users/index.blade.php）の作成方法をまとめます。</br>
                この画面では、標準のヘッダー（Dashboardロゴなど）を排除し、</br>
                実用的な「新規ユーザー登録」ボタンを備えた構成にします。
            </p>
        </li>
    </ol>

    <h3 id="monthly-aggregation">4-3. 月次集計・確定</h3>
    <p>この機能を実装するために必要なファイル一式と、それぞれの役割、具体的なコード内容をまとめました。</p>
    <ol>
        <li><strong>司令塔 (Controller)</strong>
            <p>
                月次集計の表示、CSV出力、そして「確定（ロック）」処理を担当します。</br>
                ファイル名: app/Http/Controllers/AttendanceController.php (既存のファイルに追記)
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; max-height: 300px; overflow-y: auto;"><code>&lt;?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * 月次集計画面の表示
     */
    public function monthlyReport(Request $request)
    {
        $targetMonth = $request->input('month', Carbon::now()->format('Y-m'));
        
        // 全ユーザーの指定月の勤怠データを取得
        // ユーザー名や社員番号を表示するため UserDetail もロード
        $users = User::with(['detail', 'attendances' => function($query) use ($targetMonth) {
            $query->where('work_date', 'like', "$targetMonth%");
        }])->get();

        return view('admin.monthly_report', compact('users', 'targetMonth'));
    }

    /**
     * 月次確定（一括ロック）処理
     */
    public function lockMonth(Request $request)
    {
        $month = $request->input('month');

        // 指定月の全ユーザーのデータを「承認済(2)」に更新してロックする
        Attendance::where('work_date', 'like', "$month%")
            ->where('is_locked', '<', 2)
            ->update(['is_locked' => 2]);

        return back()->with('success', "{$month}分のデータを確定（ロック）しました。");
    }
}
</code></pre>
        </li>

        <li><strong>URLの設定 (Routes)</strong>
            <p>
                管理者のみがアクセスできるよう、ミドルウェアで保護して定義します。</br>
                ファイル名: routes/web.php</br>
            </P>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0;"><code>Route::middleware(['auth', 'can:admin'])->group(function () {
    // 1. 集計画面の表示
    Route::get('/admin/monthly-report', [AttendanceController::class, 'adminMonthlyReport'])->name('admin.monthly_report');
    
    // 2. オートメーション実行 (POST)
    Route::post('/admin/monthly-report/automate', [AttendanceController::class, 'automateMonth'])->name('admin.monthly.automate');
    
    // 3. 実行結果の表示 (GET)
    Route::get('/admin/monthly-report/result', [AttendanceController::class, 'showAutomationResult'])->name('admin.monthly.result');

    // 4. 個別の確定・解除
    Route::post('/admin/finalize', [AttendanceController::class, 'finalizeMonth'])->name('admin.finalize');
    Route::post('/admin/reopen', [AttendanceController::class, 'reopenMonth'])->name('admin.reopen');
});</code></pre>
        </li>

        <li><strong>月次集計画面 (View)</strong>
            <p>
                管理者専用の「月次集計・確定」画面（admin/monthly-report）を、x-app-layout を使わずに、</br>
                オートメーション承認機能を含めた形で作成・修正する方法をまとめます。</br>
                この画面は、全社員の勤務状況を一括で確認し、問題がなければ「オートメーション承認」を</br>
                実行する、管理者業務の要となる画面です。</br>
                各ユーザーの稼働合計時間を一覧表示し、確定ボタンを配置します。</br>
                ファイル名:<cord>resources/views/admin/monthly_report.blade.php</cord></br>
                以下の内容を新しいデザインとして保存します。</br>

                <span onclick="openCodeModal('codeBlock_monthly_report')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    HTML
                </span>
            </P>

            <div id="codeBlock_monthly_report" style="display: none;">
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; text-align: left;"><code>@php
            $path = base_path('resources/views/admin/monthly_report.blade.php');
            if (file_exists($path)) {
                echo e(file_get_contents($path));
            } else {
                echo "ファイルが見つかりません: " . $path;
            }
        @endphp</code></pre>
            </div>
        </li>

        <li><strong>モデルとデータの整合性 (Model)</strong>
            <p>
                集計を正しく行うために、Attendance モデルで数値計算ができるようになっているか確認します。</br>
                ファイル名: app/Models/Attendance.php</br>
                ※特に修正は不要ですが、work_time や overtime が DB 上で decimal や float 型になっていることが前提です。
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0;"><code>&lt;?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'work_date', 'attendance_type', 'workplace', 
        'shift', 'start_time', 'end_time', 'break_time', 
        'overtime', 'work_time', 'is_locked'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}</code></pre>
        </li>

        <li><strong>構築のロードマップ</strong>
            <ol>
                <li><strong>Controllerの実装：</strong>
                    <ul>monthlyReport メソッドで、sum() や count() を使って集計ロジックを組みます。</ul>
                </li>
                <li><strong>Routeの登録:</strong>
                    <ul>管理者専用ルートとして設定。</ul>
                </li>
                <li><strong>Viewの作成:</strong>
                    <ul>monthly_report.blade.php を作成</br>
                        社員ごとの合計値をループで回して表示します。
                    </ul>
                </li>
                <li><strong>確定ボタンの動作確認:</strong>
                    <ul>・ボタンを押した際、DBの attendances テーブルの is_locked カラムが 2 に一括更新されるかを確認します。</ul>
                    <ul>・2 になったデータは、一般社員の attendance.blade.php 側で readonly になるよう制御されている必要があります。</ul>
                </li>
            </ol>
            <p>
                この月次集計機能により、給与計算に必要な「誰が何時間働いたか」のデータを一目で把握し、</br>
                不正な後出し修正を防ぐためのロック（確定）が可能になります。
            </p>
        </li>
    </ol>

    <h3 id="Approval-of-leave/overtime-requests">4-4. 休暇・残業申請承認</h3>

    <p>
        休暇・残業申請（Leave Applications）の承認画面についても、x-app-layout を使用せず、</br>
        これまでに作成した「管理者メニュー」や「社員名簿」とデザインを統一した形で作成します。</br>
        この画面は、社員から届いた申請を管理者が一覧で確認し、「承認」または「却下」をワンクリックで</br>
        実行できるインターフェースにします。
    </P>
    <ol>
        <li><strong>データベース設計 (既存テーブルの活用)</strong>
            <p>
                データを保存するテーブルを作成します。</br>
                （既に attendances テーブルがあるのであれば、本作業は不要です。）</br>
                ファイル名: database/migrations/xxxx_xx_xx_create_attendances_table.php
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0;"><code>public function up()
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
            <p>
                承認フローに以下のカラムが正しく定義されているか確認してください。
                <ul>・is_locked: 0（未確定）、1（本人確定/承認待ち）、2（管理者承認済）、3（却下）</ul>
                <ul>・attendance_type: 「有給」「欠勤」「特休」など</ul>
                <ul>・overtime: 残業時間（数値）</ul>
            </P>
        </li>

        <li><strong>司令塔 (Controller)</strong>
            <p>
                勤務表の承認に特化したメソッドを AttendanceController に追加します。</br>
                ファイル名: app/Http/Controllers/AttendanceController.php
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; max-height: 300px; overflow-y: auto;"><code>&lt;?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * 管理者用：承認待ちの申請一覧を表示
     */
    public function adminApproveIndex()
    {
        // ステータスが1（確定済み/承認待ち）のデータを取得
        // ユーザー情報と詳細情報をEager Loadして効率化
        $pendingAttendances = Attendance::with('user.detail')
            ->where('is_locked', 1) 
            ->orderBy('work_date', 'asc')
            ->get();

        return view('admin.attendance_approve', compact('pendingAttendances'));
    }

    /**
     * 承認・却下の実行
     */
    public function updateLockStatus(Request $request, Attendance $attendance)
    {
        // 管理者権限チェック
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => '権限がありません'], 403);
        }

        // status: 2(承認), 3(却下) を受け取る
        $attendance->update([
            'is_locked' => $request->status
        ]);

        return response()->json(['message' => 'ステータスを更新しました']);
    }
}</code></pre>
        </li>

        <li><strong>URLの設定 (Routes)</strong>
            <p>
                管理者がアクセスする専用のURLを定義します。</br>
                LeaveApplicationController のメソッドと紐付けます。</br>
                ファイル名: routes/web.php</br>
                PHP
            </p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0;"><code>Route::middleware(['auth', 'can:admin'])->group(function () {
    // 休暇申請：一覧表示
    Route::get('/admin/leave-requests', [LeaveApplicationController::class, 'adminIndex'])->name('admin.leave_requests');
    
    // 承認・却下アクション
    Route::post('/admin/leave-requests/{leaveApplication}/approve', [LeaveApplicationController::class, 'approve'])->name('admin.leave_approve');
    Route::post('/admin/leave-requests/{leaveApplication}/reject', [LeaveApplicationController::class, 'reject'])->name('admin.leave_reject');
});</code></pre>
        </li>

        <li><strong>管理者用承認画面 (View)</strong>
            <p>
                申請内容を一目で確認し、ワンクリックで承認できる画面を作成します。</br>
                ファイル名: resources/views/admin/automation_result.blade.php</br>
                以下の内容でファイルを新規作成、または上書き保存してください。</br>
                <span onclick="openCodeModal('codeBlock_automation_result')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    HTML
                </span>
            </p>

            <div id="codeBlock_automation_result" style="display: none;">
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

        <li><strong>構築の手順まとめ</strong>
            <ol>
                <li><strong>モデルのリレーション確認：</strong>Attendance.php で user() リレーションが定義されていること。</li>
                <li><strong>Controller実装：</strong>管理者用の adminApproveIndex メソッドを作成。</li>
                <li><strong>Route登録：</strong>管理者ミドルウェア（admin）を適用したルートを追加。</li>
                <li><strong>View作成：</strong> attendance_approve.blade.php を作成し、一覧が表示されるか確認。/li>
                <li><strong>ナビゲーション追加：</strong>
                    <ul>・一般社員の attendance.blade.php で、本人が入力を終えたら is_locked を 1 に更新する</br>
                        「確定ボタン」を実装（※既に実装済みの場合は不要）。</ul>
                    <ul>・管理者の画面に「承認待ち一覧へ」のリンクボタンを追加。</ul>
                </li>
            </ol>
            <p>これで、社員が勤務表を「確定」すると管理者の承認リストに載り、管理者が承認することで最終的な</br>
                勤怠データとしてロックされるフローが完成します。</P>

        <li>反映の手順
            <ol>
                <li><strong>ファイルの配置：</strong></br>
                    <ul>ローカルで作成した leave_requests.blade.php を AWS（パブリック IPv4 アドレス）の </br>
                        /var/www/html/Attendance/resources/views/admin/ へアップロードしてください。
                    </ul>
                </li>
                <li><strong>コントローラーの確認：</strong></br>
                    <ul>LeaveApplicationController.php 内の adminIndex メソッドが、</br>
                        return view('admin.leave_requests', compact('leaveApplications')); のように</br>
                        正しいビュー名と変数を返しているか確認してください。</ul>
                </li>
                <li><strong>運用のアドバイス：</strong></br>
                    <ul>この画面では「pending（保留）」状態の申請だけが操作可能で、一度「承認」</br>
                        または「却下」したものはボタンが消える設計にしています。</br>
                        これにより、二重処理のミスを防ぐことができます。</br>
                        また、申請種別（有給・残業など）がひと目で分かるように type_label などの</br>
                        アクセサ（Model側で定義）を利用すると、より使いやすくなります。</ul>
                </li>
            </ol>
        </li>
    </ol>

    <h3 id="Expense-reimbursement-approval">4-5. 経費精算承認</h3>
    <p>
        経費精算管理の承認画面（admin/expenses）を他の管理者画面とデザインを統一し、かつ x-app-layout を使わずに作成する方法をまとめます。</br>
        社員が申請した交通費や備品代などの内容（金額、領収書、目的）をチェックし、一括または個別で「承認」や「却下」を行うためのものです。
    </p>
    <ol>
        <li><strong>司令塔 (Controller)</strong>
            <P>既に作成した ExpenseController.php に、管理者用のメソッドを追加・整理します。</br>
                ファイル名: app/Http/Controllers/ExpenseController.php</br>
                <span onclick="openCodeModal('codeBlock_Expense_Controller')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    PHP
                </span>
            </P>

            <div id="codeBlock_Expense_Controller" style="display: none;">
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

        <li><strong>URLの設定 (Routes)</strong>
            <p>
                管理者専用のURLを定義します。</bk>
                ファイル名: routes/web.php
            </p>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0;"><code>use App\Http\Controllers\ExpenseController;

Route::middleware(['auth', 'admin'])->group(function () {
    // 管理者用の一覧画面
    Route::get('/expenses/admin', [ExpenseController::class, 'adminIndex'])->name('expenses.admin');
    
    // 承認・却下実行（PATCHメソッドを使用）
    Route::patch('/expenses/{expense}/status', [ExpenseController::class, 'updateStatus'])->name('expenses.updateStatus');
});</code></pre>
        </li>

        <li><strong>管理者用画面 (View)</strong>
            <p>
                承認ボタンや却下理由入力フォームを備えた専用画面を作成します。</br>
                ファイル名: resources/views/expenses_admin.blade.php</br>
                以下の内容を新しいデザインとして保存します。</br>
                <span onclick="openCodeModal('codeBlock_expenses_admin')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    HTML
                </span>
            </p>

            <div id="codeBlock_expenses_admin" style="display: none;">
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; text-align: left;"><code>@php
            $path = base_path('resources/views/expenses_admin.blade.php');
            if (file_exists($path)) {
                echo e(file_get_contents($path));
            } else {
                echo "ファイルが見つかりません: " . $path;
            }
        @endphp</code></pre>
            </div>
        </li>

        <li><strong>モデルのリレーション確認 (Model)</strong>
            <p>既に作成済みかと思いますが、Expense モデルが User を参照できるように設定されている必要があります。</br>
                ファイル名: app/Models/Expense.php</br>
                PHP
            </p>

        </li>

        <li><strong>構築のロードマップ</strong>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>public function user()
{
    return $this->belongsTo(User::class);
}</code></pre>

        <li><strong>構築のロードマップ</strong>
            <ol>
                <li><strong>Route設定：</strong>web.php に /expenses/admin へのルートを記述。</li>
                <li><strong>Controller編集：</strong>ExpenseController に adminIndex と updateStatus を実装。</li>
                <li><strong>View作成：</strong>expenses_admin.blade.php を作成。</li>
                <li><strong>リンクの導線：</strong></br>
                    <ul>一般用の expenses.blade.php に、管理者だけが見える<br>
                        「承認パネルへ」ボタンを設置（&#64;if(Auth::user()->is_admin) を使用）。
                    </ul>
            </ol>
            <p>この構築により、「一般社員が申請」→「管理者が承認パネルで確認・処理」→</br>
                「一般社員の画面に結果が反映」というサイクルが完成します。</p>
        </li>
    </ol>

    <h3 id="Operation-guide">4-6. 操作ガイド</h3>
    <p>
        管理者メニュー（admin/menu）に、既存のPDFマニュアル（/manual）へ遷移する</br>
        カード型ボタンを追加した際の手順とファイル内容をまとめます。</br>
        この実装では、LaravelのBladeテンプレートを使用して、HTML構造とスタイルを定義しています。
    </p>
    <ol>
        <li><strong>リンク先（ルーティング）の定義</strong>
            <p>
                まず、/manual というURLが叩かれたときにPDFファイルをブラウザで開く処理を定義します。</br>
                ファイル名: routes/web.php
            </P>
            <p style="margin-bottom: 5px; font-size: 1rem;">PHP</p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0;"><code>// 管理者権限（can:admin）グループの中に記述
Route::get('/manual', function () {
    // public/pdf フォルダ内の実体ファイルを指定
    $path = public_path('pdf/kintai-app_figure.pdf');

    if (!file_exists($path)) {
        abort(404, 'マニュアルファイルが見つかりません。');
    }

    // ブラウザで直接開くためのレスポンスを返す
    return Response::file($path, [
        'Content-Type' =&gt; 'application/pdf',
        'Content-Disposition' =&gt; 'inline; filename="kintai-app_manual.pdf"'
    ]);
})-&gt;name('manual.show');</code></pre>
        </li>

        <li><strong>メニュー画面へのボタン追加</strong>
            <p>
                管理者メニューのHTMLファイルに、ボタン（カード）を追記します。</br>
                ファイル名: resources/views/admin/selector.blade.php
            </p>
            <ol>
                <li><strong>スタイル（CSS）の定義</strong></br>
                    <p>
                        &lt;head&gt; タグ内の &lt;style&gt; ブロックに、マニュアルカード専用のデザイン（グレーのアクセント）を定義しています。</br>
                        CSS
                    </p>
                    <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0;"><code>&#123;&#123;-- 操作マニュアルカード --&#125;&#125;
&lt;a href="&#123;&#123; route('manual.show') &#125;&#125;" target="_blank" class="menu-card manual-card"&gt;
    &lt;span class="icon"&gt;📖&lt;/span&gt;
    &lt;span class="title"&gt;操作マニュアル&lt;/span&gt;
    &lt;p class="desc"&gt;システムの操作図解（PDF）を表示します。別タブで開きます。&lt;/p&gt;
&lt;/a&gt;</code></pre>
                </li>
            </ol>
        </li>

        <li><strong>ボタンの構成要素と役割</strong>
            <p>作成したボタンには、利便性を高めるための工夫が施されています。</p>
            <ol>
                <ul><strong>target="_blank"：</strong></br>
                    <ul>リンクを別タブで開く設定です。</br>マニュアルを見ながらシステムを操作できるようにしています。</ul>
                </ul>
                <ul><strong>route('manual.show')：</strong></br>
                    <ul>web.php で設定したルート名を指定することで、URLが変更になっても</br>リンク切れを防ぎます。</ul>
                </ul>
                <ul><strong>class="menu-card"：</strong></br>
                    <ul>他のメニュー（社員名簿や勤怠集計）と統一感のあるデザインを</br>適用しています。</ul>
                </ul>
                <ul><strong>絵文字（📖）: </strong></br>
                    <ul>視覚的に「本・説明書」であることを直感的に伝えます。</ul>
                </ul>
            </ol>
        </li>

        <li>
            <strong>ファイルの配置場所（重要）</strong>
            <p>
                リンクを正常に動作させるために、PDFファイルは以下の場所に配置されている必要があります。</br>
                <strong>・PDFの実体:</strong>public/pdf/kintai-app_figure.pdf</br>
                この手順により、管理者メニューからワンクリックでPDFマニュアルを別画面に表示できるようになります。
            </p>
        </li>
    </ol>

    <h3 id="Construction-Guide-with-Table-of-Contents)">4-7. 構築ガイド（目次付）</h3>
    <p>
        http://attendance.test/guidebook で、目次付きのPDFビューワーを表示するための構築手順をまとめます。</br>
        この機能を実現するには、Laravel（バックエンド）でリクエストを受け取り、</br>
        Inertia.js を介して React（フロントエンド）のコンポーネントを描画する流れを構築する必要があります。
    </p>
    <ol>
        <li><strong>サーバー側：ルーティングの設定</strong></br>
            <p>
                ブラウザから /guidebook にアクセスがあった際、Reactコンポーネントを呼び出すように設定します。</br>
                ファイル名: routes/web.php</br>
                既存の view('guidebook') を Inertia::render に書き換えるのが最大のポイントです。</br>
                PHP
            </p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; "><code>use Inertia\Inertia;

// ...（他のルート）

Route::middleware(['auth', 'verified', 'can:admin'])->group(function () {
    // Reactコンポーネント「GuideView」を呼び出す
    Route::get('/guidebook', function () {
        return Inertia::render('GuideView');
    })->name('guidebook.show');
});</code></pre>
        </li>

        <li><strong>フロント側：PDFビューワーコンポーネントの作成</strong></br>
            <p>
                目次、ページ送り、PDF表示の実体となるファイルを作成します。</br>
                ファイル名:<cord>resources/js/Pages/GuideView.jsx</cord></br>
                react-pdf ライブラリを使用して、PDFの制御を行います。</br>
                <span onclick="openCodeModal('codeBlock_GuideView')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
                    JavaScript
                </span>
            </p>
            <div id="codeBlock_GuideView" style="display: none;">
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; text-align: left;"><code>@php
            $path = base_path('resources/js/Pages/GuideView.jsx');
            if (file_exists($path)) {
                echo e(file_get_contents($path));
            } else {
                echo "ファイルが見つかりません: " . $path;
            }
        @endphp</code></pre>
            </div>
        </li>

        <li><strong>PDFファイルの配置</strong></br>
            <p>Reactが読み込める場所にPDFファイルを置きます。</p>
            <ul><strong>物理的な配置: </strong>storage/app/public/guidebook.pdf にファイルを保存します。</ul>
            <ul>
                <strong>公開設定: </strong>以下のコマンドを実行して、ブラウザから見えるようにします。</br>
                Bash</br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 0;"><code>php artisan storage:link</code></pre>
                ※ これにより public/storage/guidebook.pdf というパスでアクセス可能になります。
            </ul>
        </li>

        <li><strong>ビルドと実行</strong></br>
            <p>
                最後に、作成したJSXファイルをJavaScriptにコンパイルします。</br>
                <ol>
                    <li><strong>イブラリ未導入の場合:</strong></br>
                        Bash</br>
                        <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>npm install react-pdf --legacy-peer-deps</code></pre>
                    </li>

                    <li><strong>ビルドの実行:</strong></br>
                        <ol>
                            <ul>・ローカル環境: npm run dev を起動したままにします。</ul>
                            <ul>・本番環境: npm run build を実行してファイルを生成します。</ul>
                        </ol>
                    </li>
                </ol>
                これで、指定のURLにアクセスすると目次付きの操作ガイドが表示されます。<br>
                もし「View not found」が出る場合は、web.php が view() ではなく Inertia::render() になっているか再確認してください。
            </p>
        </li>
    </ol>
</section>
