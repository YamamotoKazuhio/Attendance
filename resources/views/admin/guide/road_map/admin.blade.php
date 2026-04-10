<section>
    <h2 id="admin">4. 管理者のみのページの作成</h2>
    <h3 id="administrator-only-menu">4-1. 管理者専用メニュー</h3>
    <p>管理者専用メニュー（Admin Dashboard）は、各管理機能（経費承認、勤務承認、名簿管理、月次集計）への入り口となる重要なハブです。</br>
        これを構築するために必要なファイル一式と、各機能へ安全にアクセスするための「管理者認証」を含めた手順をまとめました。</p>
    <ol>
        <li>司令塔 (Controller)
            <p>
                ダッシュボードを表示するだけのシンプルなコントローラーですが、ここで「各機能の未処理件数」などを取得して表示させると利便性が高まります。</br>
                ファイル名: app/Http/Controllers/AdminController.php</br>
                PHP<br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;?php

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
            </P>
        </li>

        <li>アクセス制限 (Middleware)
            <p>
                一般社員が管理者メニューにURLを直接入力して入ってこられないようガードをかけます。</br>
                ファイル名: app/Http/Middleware/AdminMiddleware.php</br>
                ※ php artisan make:middleware AdminMiddleware で作成<br>
                PHP<br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;?php

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
                ※作成後、bootstrap/app.php（Laravel 11）または Kernel.php（Laravel 10以前）で、このミドルウェアを admin という名前で登録してください。
            </p>

        </li>
        <li>URLの設定 (Routes)
            <p>
                管理者機能を一括でグループ化し、先ほどのミドルウェアで保護します。</br>
                ファイル名: routes/web.php<br>
                PHP</br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>use App\Http\Controllers\AdminController;
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

        <li>管理者専用メニュー画面 (View)
            <p>
                各機能へアクセスするためのパネル（カード型デザイン）を作成します。</br>
                x-app-layout を使わず、独自のスタイルを適用して「勤怠管理」や「ユーザー管理」へのボタンを配置します。</br>
                ファイル名: resources/views/admin/dashboard.blade.php<br>
                HTML<br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;!DOCTYPE html&gt;
&lt;html lang="ja"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;管理者メニュー&lt;/title&gt;
    &lt;style&gt;
        .menu-container { display: flex; flex-wrap: wrap; gap: 20px; padding: 20px; justify-content: center; }
        .menu-card { 
            width: 250px; padding: 30px; border: 1px solid #ddd; border-radius: 8px; 
            text-align: center; text-decoration: none; color: #333; transition: 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .menu-card:hover { transform: translateY(-5px); box-shadow: 0 6px 12px rgba(0,0,0,0.15); border-color: #ff9800; }
        .icon { font-size: 40px; margin-bottom: 15px; display: block; }
        .title { font-size: 18px; font-weight: bold; }
        .desc { font-size: 14px; color: #666; margin-top: 10px; }
        .highlight { border-top: 5px solid #ff9800; }
        .manual-card { border-top: 5px solid #6c757d; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1 style="text-align: center; margin-top: 40px;"&gt;管理者専用メニュー&lt;/h1&gt;

    &lt;div class="menu-container"&gt;
        &lt;a href="&#123;&#123; route('admin.users.index') &#125;&#125;" class="menu-card"&gt;
            &lt;span class="icon"&gt;👥&lt;/span&gt;
            &lt;span class="title"&gt;社員名簿管理&lt;/span&gt;
            &lt;p class="desc"&gt;全社員の基本情報・連絡先の確認および編集を行います。&lt;/p&gt;
        &lt;/a&gt;

        &lt;a href="&#123;&#123; route('admin.monthly_report') &#125;&#125;" class="menu-card"&gt;
            &lt;span class="icon"&gt;📊&lt;/span&gt;
            &lt;span class="title"&gt;月次集計・確定&lt;/span&gt;
            &lt;p class="desc"&gt;全社員の稼働時間確認と、月次データの確定処理を行います。&lt;/p&gt;
        &lt;/a&gt;

        &lt;a href="&#123;&#123; route('admin.leave_requests') &#125;&#125;" class="menu-card highlight"&gt;
            &lt;span class="icon"&gt;📩&lt;/span&gt;
            &lt;span class="title"&gt;休暇・残業申請承認&lt;/span&gt;
            &lt;p class="desc"&gt;社員から届いた有給や残業の事前申請を確認・承認します。&lt;/p&gt;
        &lt;/a&gt;

        &lt;a href="&#123;&#123; route('expenses.admin') &#125;&#125;" class="menu-card"&gt;
            &lt;span class="icon"&gt;💰&lt;/span&gt;
            &lt;span class="title"&gt;経費精算管理&lt;/span&gt;
            &lt;p class="desc"&gt;提出された経費精算書の確認と承認・却下を行います。&lt;/p&gt;
        &lt;/a&gt;

        &lt;a href="&#123;&#123; route('attendance.index') &#125;&#125;" class="menu-card"&gt;
            &lt;span class="icon"&gt;📅&lt;/span&gt;
            &lt;span class="title"&gt;勤務表画面へ&lt;/span&gt;
            &lt;p class="desc"&gt;自身の勤務表、または特定社員の勤務表入力画面に戻ります。&lt;/p&gt;
        &lt;/a&gt;

        &lt;a href="&#123;&#123; url('/manual') &#125;&#125;" target="_blank" class="menu-card manual-card"&gt;
            &lt;span class="icon"&gt;📖&lt;/span&gt;
            &lt;span class="title"&gt;操作ガイド&lt;/span&gt;
            &lt;p class="desc"&gt;システムの操作図解（PDF）を表示します。別タブで開きます。&lt;/p&gt;
        &lt;/a&gt;

        &lt;a href="&#123;&#123; route('guide.show') &#125;&#125;" class="menu-card highlight" style="border-top-color: #4caf50;"&gt;
            &lt;span class="icon"&gt;💡&lt;/span&gt;
            &lt;span class="title"&gt;構築ガイド (目次付)&lt;/span&gt;
            &lt;p class="desc"&gt;目次からページを飛ばせる、React製の高機能ガイドブックを表示します。&lt;/p&gt;
        &lt;/a&gt;
    &lt;/div&gt;

    &lt;div style="text-align: center; margin-top: 30px;"&gt;
        &lt;form method="POST" action="&#123;&#123; route('logout') &#125;&#125;"&gt;
            &#64;csrf
            &lt;button type="submit" style="background: none; border: none; color: blue; text-decoration: underline; cursor: pointer;"&gt;サインアウト&lt;/button&gt;
        &lt;/form&gt;
    &lt;/div&gt;
&lt;/body&gt;
&lt;/html&gt;
</code></pre>

            </p>
        </li>

        <li>構築のロードマップ
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
                各画面（admin/users/index.blade.php など）のヘッダー付近に以下を追加します。</br>
                HTML</br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;a href="&#123;&#123; route('admin.menu') &#125;&#125;" style="text-decoration: none; color: #1a73e8;"&gt;← 管理者メニューへ戻る&lt;/a&gt;</code></pre>
            </p>
        </li>
    </ol>
    </p>

    <h3 id="employee-roster-management">4-2. 社員名簿管理</h3>
    <p>
        社員名簿管理（User Details Management）は、システムの基盤となるユーザー情報（社員番号、氏名、入社日、権限など）を管理する重要な機能です。</br>
        Laravelの標準的な認証機能（BreezeやJetstreamなど）に加え、実務に必要な詳細情報を管理するための「UserDetail」モデルを分離して構築する手順をまとめました。
    </p>
    <ol>
        <li>データベース設計図 (Migration)
            <p>
                標準の users テーブルとは別に、社員固有の情報を保存する user_details テーブルを作成します。</br>
                ファイル名: database/migrations/xxxx_xx_xx_create_user_details_table.php</br>
                PHP</br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>public function up()
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
            </P>
        </li>

        <li>データの窓口 (Model)
            <p>
                UserモデルとUserDetailモデルの紐付け（1対1のリレーション）を設定します。</br>
                ファイル名: app/Models/UserDetail.php</br>
                PHP</br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;?php

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
            ファイル名: app/Models/User.php (既存ファイルに追記)</br>
            PHP</br>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>public function detail() {
    return $this->hasOne(UserDetail::class);
}</code></pre>
            </p>
        </li>

        <li>
            司令塔 (Controller)
            <p>
                名簿の一覧表示、新規登録、編集、削除を処理します。</br>
                ファイル名: app/Http/Controllers/UserDetailController.php</br>
                PHP</br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;

class UserDetailController extends Controller
{
    // 名簿一覧
    public function index()
    {
        $users = User::with('detail')->get();
        return view('admin.users.index', compact('users'));
    }

    // 編集画面
    public function edit(User $user)
    {
        $user->load('detail');
        return view('admin.users.edit', compact('user'));
    }

    // 更新処理
    public function update(Request $request, User $user)
    {
        $user->update(['email' => $request->email]);
        
        $user->detail()->updateOrCreate(
            ['user_id' => $user->id],
            $request->only(['employee_code', 'name_kanji', 'department', 'daily_commute_fee'])
        );

        return redirect()->route('admin.users.index')->with('success', '社員情報を更新しました。');
    }
}</code></pre>
            </p>
        </li>

        <li>URLの設定 (Routes)
            <p>
                管理画面用のURLをグループ化して定義します。</br>
                ファイル名: routes/web.php</br>
                PHP</br>
            </p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>use App\Http\Controllers\UserDetailController;

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/users', [UserDetailController::class, 'index'])->name('admin.users.index');
    Route::get('/users/{user}/edit', [UserDetailController::class, 'edit'])->name('admin.users.edit');
    Route::patch('/users/{user}', [UserDetailController::class, 'update'])->name('admin.users.update');
});</code></pre>
        </li>

        <li>見た目 (View)
            <p>
                一覧画面と編集画面を作成します。</br>
                ファイル名: resources/views/admin/users/index.blade.php</br>
                以下の内容でファイルを新規作成、または上書き保存してください。</br>
                保存パス: resources/views/admin/users/index.blade.php</br>
                HTML</br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>@verbatim &lt;!DOCTYPE html&gt;
&lt;html lang="ja"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;社員情報一覧&lt;/title&gt;
    &lt;style&gt;
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #f4f7f6; padding: 20px; color: #333; }
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        
        .header-area { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .nav-back { text-decoration: none; color: #1976d2; font-weight: bold; font-size: 0.9em; }
        .nav-back:hover { text-decoration: underline; }
        
        .tab-area { margin-bottom: 20px; display: flex; gap: 10px; }
        .tab-btn { padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9em; font-weight: bold; }
        .tab-active { background: #1976d2; color: white; }
        .tab-inactive { background: #e0e0e0; color: #666; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #fff; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 12px 8px; text-align: left; word-break: break-all; font-size: 0.9em; }
        th { background: #f8f9fa; color: #555; font-weight: bold; position: sticky; top: 0; }
        
        /* 列幅の調整 */
        .col-code { width: 100px; }
        .col-name { width: 150px; }
        .col-email { width: 200px; }
        .col-date { width: 120px; }
        .col-years { width: 80px; }
        .col-paid { width: 80px; }
        .col-admin { width: 70px; }
        .col-action { width: 120px; }

        tr:hover { background-color: #f1f8ff; }
        .bg-retired { background-color: #f9f9f9; color: #999; }

        .text-center { text-align: center; }
        
        .badge-admin { 
            color: #d32f2f; 
            font-weight: bold;
            font-size: 1.2em;
        }
        .badge-retired {
            background: #9e9e9e;
            color: white;
            font-size: 0.7em;
            padding: 2px 4px;
            border-radius: 3px;
            margin-left: 4px;
        }

        .btn-action { text-decoration: none; font-weight: bold; margin-right: 8px; }
        .btn-edit { color: #1976d2; }
        .btn-delete { color: #e53935; border: none; background: none; cursor: pointer; padding: 0; font-family: inherit; font-size: inherit; }
        .btn-restore { color: #388e3c; border: none; background: none; cursor: pointer; padding: 0; font-family: inherit; font-size: inherit; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;div class="container"&gt;
        &lt;div class="header-area"&gt;
            &lt;h2 style="margin: 0;"&gt;👥 社員情報一覧&lt;/h2&gt;
            &lt;a href="{{ route('admin.menu') }}" class="nav-back"&gt;← 管理メニューへ戻る&lt;/a&gt;
        &lt;/div&gt;
        
        &lt;div style="margin: 20px 0; display: flex; justify-content: flex-end;"&gt;
            &lt;a href="{{ route('admin.register') }}"
                style="background-color: #28a745; color: white; padding: 10px 22px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; box-shadow: 0 2px 5px rgba(0,0,0,0.15); display: inline-flex; align-items: center; gap: 8px;"&gt;
                &lt;span style="font-size: 18px;"&gt;+&lt;/span&gt; 新規ユーザー登録
            &lt;/a&gt;
        &lt;/div&gt;
        
        &lt;div class="tab-area"&gt;
            &lt;a href="{{ route('admin.users.index') }}" 
                class="tab-btn {{ request('tab') !== 'all' ? 'tab-active' : 'tab-inactive' }}"&gt;
                在職中
            &lt;/a&gt;
            &lt;a href="{{ route('admin.users.index', ['tab' => 'all']) }}" 
                class="tab-btn {{ request('tab') === 'all' ? 'tab-active' : 'tab-inactive' }}"&gt;
                すべて（退職者含む）
            &lt;/a&gt;
        &lt;/div&gt;

        &lt;table&gt;
            &lt;thead&gt;
                &lt;tr&gt;
                    &lt;th class="col-code"&gt;社員番号&lt;/th&gt;
                    &lt;th class="col-name"&gt;氏名&lt;/th&gt;
                    &lt;th class="col-email"&gt;アカウント名&lt;/th&gt;
                    &lt;th class="col-date"&gt;入社日&lt;/th&gt;
                    &lt;th class="col-years"&gt;勤続年数&lt;/th&gt;
                    &lt;th class="col-paid"&gt;有給残数&lt;/th&gt;
                    &lt;th class="col-admin text-center"&gt;管理者&lt;/th&gt;
                    &lt;th class="col-action text-center"&gt;操作&lt;/th&gt;
                &lt;/tr&gt;
            &lt;/thead&gt;
            &lt;tbody&gt;
                @foreach($users as $user)
                &lt;tr class="{{ $user-&gt;trashed() ? 'bg-retired' : '' }}"&gt;
                    {{-- 社員番号 --}}
                    &lt;td class="text-center"&gt;
                        {{ $user-&gt;detail-&gt;employee_code ?? ($user-&gt;employee_code ?? '---') }}
                    &lt;/td&gt;

                    {{-- 氏名 --}}
                    &lt;td&gt;
                        {{ $user-&gt;detail-&gt;name_kanji ?? ($user-&gt;name ?? '---') }}
                        @if($user-&gt;trashed())
                            &lt;span class="badge-retired"&gt;退職済&lt;/span&gt;
                        @endif
                    &lt;/td&gt;

                    {{-- アカウント名 --}}
                    &lt;td&gt;{{ $user-&gt;email }}&lt;/td&gt;

                    {{-- 入社日 --}}
                    &lt;td&gt;{{ $user-&gt;formatted_joined_at }}&lt;/td&gt;

                    {{-- 勤続年数 --}}
                    &lt;td class="text-right"&gt;{{ $user-&gt;experience_years }}年&lt;/td&gt;

                    {{-- 有給残数 --}}
                    &lt;td class="text-right"&gt;{{ $user-&gt;remaining_paid }}日&lt;/td&gt;

                    {{-- 管理者フラグ --}}
                    &lt;td class="text-center"&gt;
                    @if($user-&gt;is_admin)
                        &lt;span class="badge-admin"&gt;○&lt;/span&gt;
                    @endif
                    &lt;/td&gt;

                    {{-- 操作ボタン --}}
                    &lt;td class="text-center"&gt;
                        @if($user-&gt;trashed())
                        &lt;form action="{{ route('admin.users.restore', $user-&gt;id) }}" method="POST" style="display:inline;"&gt;
                        @csrf
                            &lt;button type="submit" class="btn-restore btn-action" onclick="return confirm('在職状態に戻しますか？')"&gt;
                                復元
                            &lt;/button&gt;
                        &lt;/form&gt;
                        @else
                        &lt;a href="{{ route('user_details.show', $user-&gt;id) }}" class="btn-edit btn-action"&gt;詳細&lt;/a&gt;
                        &lt;form action="{{ route('admin.users.destroy', $user-&gt;id) }}" method="POST" style="display:inline;"&gt;
                        @csrf
                        @method('DELETE')
                        &lt;button type="submit" class="btn-delete btn-action" onclick="return confirm('本当に削除（退職処理）しますか？')"&gt;
                            削除
                        &lt;/button&gt;
                        &lt;/form&gt;
                        @endif
                    &lt;/td&gt;
                &lt;/tr&gt;
            @endforeach
            &lt;/tbody&gt;
        &lt;/table&gt;
    &lt;/div&gt;
&lt;/body&gt;
&lt;/html&gt;
@endverbatim</code></pre>
            </p>
        </li>

        <li>構築のロードマップ
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
        <li>司令塔 (Controller)
            <p>
                月次集計の表示、CSV出力、そして「確定（ロック）」処理を担当します。</br>
                ファイル名: app/Http/Controllers/AttendanceController.php (既存のファイルに追記)</br>
                PHP</br>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;?php

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
            </p>
        </li>

        <li>URLの設定 (Routes)
            <p>
                管理者のみがアクセスできるよう、ミドルウェアで保護して定義します。</br>
                ファイル名: routes/web.php</br>
                PHP</br>
            </P>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>Route::middleware(['auth', 'can:admin'])->group(function () {
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

        <li>月次集計画面 (View)
            <p>
                管理者専用の「月次集計・確定」画面（admin/monthly-report）を、x-app-layout を使わずに、</br>
                オートメーション承認機能を含めた形で作成・修正する方法をまとめます。</br>
                この画面は、全社員の勤務状況を一括で確認し、問題がなければ「オートメーション承認」を</br>
                実行する、管理者業務の要となる画面です。</br>
                各ユーザーの稼働合計時間を一覧表示し、確定ボタンを配置します。</br>
                ファイル名: resources/views/admin/monthly_report.blade.php</br>
                以下の内容を新しいデザインとして保存します。</br>
                HTML</br>
            </P>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>@verbatim &lt;!DOCTYPE html&gt;
&lt;html lang="ja"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;{{ $targetMonth }} 月次集計一覧&lt;/title&gt;
    &lt;link rel="stylesheet" href="{{ asset('css/Attendance.css') }}"&gt;
    &lt;style&gt;
        body { background-color: #f8f9fa; font-family: sans-serif; padding: 20px; }
        .report-container { max-width: 1400px; margin: 0 auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .report-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .report-table { width: 100%; border-collapse: collapse; background: white; font-size: 13px; table-layout: fixed; }
        .report-table th, .report-table td { border: 1px solid #ccc; padding: 12px 8px; text-align: center; word-break: break-all; }
        .report-table th { background: #007bff; color: white; position: sticky; top: 0; z-index: 10; }
        .text-right { text-align: right; }
        .badge-finalized { background-color: #6c757d; color: white; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 12px; display: block; margin-top: 5px; }
        .btn-finalize { background-color: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; }
        .btn-reopen { background-color: #ffc107; color: #212529; border: 1px solid #d39e00; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; }
        .remaining-paid { font-weight: bold; background-color: #fff9db; }
        .alert-success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #c3e6cb; }
        
        /* 列幅の調整 */
        .col-emp { width: 80px; }
        .col-name { width: 150px; }
        .col-paid { width: 100px; }
        .col-work { width: 90px; }
        .col-commute { width: 100px; }
        .col-action { width: 160px; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;

&lt;div class="report-container"&gt;
    &lt;div class="report-header"&gt;
        &lt;h2 style="margin: 0;"&gt;月次稼働集計 ({{ $targetMonth }})&lt;/h2&gt;
        &lt;a href="{{ route('admin.menu') }}" style="text-decoration: none; background: #6c757d; color: white; padding: 8px 16px; border-radius: 4px;"&gt;管理メニューへ戻る&lt;/a&gt;
    &lt;/div&gt;

    {{-- フラッシュメッセージ表示エリア --}}
    @if(session('success'))
        &lt;div class="alert-success"&gt;{{ session('success') }}&lt;/div&gt;
    @endif

    &lt;div style="margin-bottom: 20px; background: #eee; padding: 15px; border-radius: 4px; display: flex; align-items: center; gap: 20px;"&gt;
        &lt;form action="{{ route('admin.monthly_report') }}" method="GET" style="margin: 0;"&gt;
            &lt;label style="font-weight: bold;"&gt;表示月選択：&lt;/label&gt;
            &lt;input type="month" name="month" value="{{ substr($targetMonth, 0, 7) }}" onchange="this.form.submit()" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;"&gt;
        &lt;/form&gt;

        &lt;form action="{{ route('admin.monthly.automate') }}" method="POST" onsubmit="return confirm('選択した日数以上の入力があるユーザーを一括承認します。よろしいですか？')" style="margin: 0; display: flex; align-items: center; gap: 10px;"&gt;
        @csrf
        &lt;input type="hidden" name="month" value="{{ $targetMonth }}"&gt;
        
        &lt;label style="font-weight: bold;"&gt;承認基準：&lt;/label&gt;
        &lt;select name="threshold_days" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;"&gt;
            &lt;option value="15"&gt;15日以上入力&lt;/option&gt;
            &lt;option value="20" selected&gt;20日以上入力&lt;/option&gt;
            &lt;option value="25"&gt;25日以上入力&lt;/option&gt;
        &lt;/select&gt;

        &lt;button type="submit" style="background-color: #ff5722; color: white; padding: 10px 20px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;"&gt;
            🚀 オートメーション一括承認
        &lt;/button&gt;
    &lt;/form&gt;
    &lt;/div&gt;

    &lt;table class="report-table"&gt;
        &lt;thead&gt;
            &lt;tr&gt;
                &lt;th class="col-emp"&gt;社員番号&lt;/th&gt;
                &lt;th class="col-name"&gt;氏名&lt;/th&gt;
                &lt;th class="col-paid"&gt;今月の有給取得日数&lt;/th&gt;
                &lt;th class="col-paid"&gt;有給残日数&lt;/th&gt;
                &lt;th class="col-work"&gt;総稼働&lt;/th&gt;
                &lt;th class="col-work"&gt;残業&lt;/th&gt;
                &lt;th class="col-commute"&gt;通勤費&lt;/th&gt;
                &lt;th class="col-action"&gt;アクション&lt;/th&gt;
            &lt;/tr&gt;
        &lt;/thead&gt;
        &lt;tbody&gt;
            @foreach($users as $user)
            &lt;tr&gt;
                {{-- 社員番号 --}}
                &lt;td&gt;{{ $user['emp_id'] }}&lt;/td&gt;
                
                {{-- 氏名 --}}
                &lt;td style="font-weight: bold; text-align: left; padding-left: 10px;"&gt;{{ $user['name'] }}&lt;/td&gt;
                
                {{-- 今月の有給取得日数 --}}
                &lt;td&gt;{{ number_format($user['monthly_paid'], 1) }}日&lt;/td&gt;
                
                {{-- 有給残日数 --}}
                &lt;td class="remaining-paid" style="color: {{ $user['remaining_paid'] <= 2 ? 'red' : 'black' }};"&gt;
                    {{ number_format($user['remaining_paid'], 1) }}日
                &lt;/td&gt;
                
                {{-- 総稼働 --}}
                &lt;td class="text-right"&gt;{{ number_format($user['total_work'], 2) }}h&lt;/td&gt;
                
                {{-- 残業 --}}
                &lt;td class="text-right"&gt;{{ number_format($user['total_ot'], 2) }}h&lt;/td&gt;
                
                {{-- 通勤費 --}}
                &lt;td class="text-right"&gt;{{ number_format($user['total_commute']) }}円&lt;/td&gt;
                
                {{-- アクション --}}
                &lt;td&gt;
                    &lt;div style="display: flex; flex-direction: column; gap: 8px; align-items: center;"&gt;
                        &lt;a href="{{ route('attendance.index', ['search_query' => $user['emp_id'], 'month' => $targetMonth]) }}" style="font-size: 12px; text-decoration: underline; color: #0056b3; font-weight: bold;"&gt;
                            勤務表を編集・確認
                        &lt;/a&gt;
                        
                        @if($user['is_finalized'])
                            &lt;form action="{{ route('admin.reopen') }}" method="POST" style="width: 100%;"&gt;
                                @csrf
                                &lt;input type="hidden" name="user_id" value="{{ $user['id'] }}"&gt;
                                &lt;input type="hidden" name="month" value="{{ $targetMonth }}"&gt;
                                &lt;button type="submit" class="btn-reopen" onclick="return confirm('この月の確定を解除して編集可能にしますか？')"&gt;
                                    確定解除
                                &lt;/button&gt;
                            &lt;/form&gt;
                            &lt;span class="badge-finalized"&gt;確定済み&lt;/span&gt;
                        @else
                            &lt;form action="{{ route('admin.finalize') }}" method="POST" style="width: 100%;"&gt;
                                @csrf
                                &lt;input type="hidden" name="user_id" value="{{ $user['id'] }}"&gt;
                                &lt;input type="hidden" name="month" value="{{ $targetMonth }}"&gt;
                                &lt;button type="submit" class="btn-finalize" onclick="return confirm('この月の内容を確定させますか？')"&gt;
                                    月次確定
                                &lt;/button&gt;
                            &lt;/form&gt;
                            &lt;span style="font-size: 11px; color: #17a2b8; font-weight: bold;"&gt;入力中...&lt;/span&gt;
                        @endif
                    &lt;/div&gt;
                &lt;/td&gt;
            &lt;/tr&gt;
            @endforeach
        &lt;/tbody&gt;
    &lt;/table&gt;
&lt;/div&gt;

&lt;/body&gt;
&lt;/html&gt;
@endverbatim </code></pre>
        </li>

        <li>
            モデルとデータの整合性 (Model)
            <p>
                集計を正しく行うために、Attendance モデルで数値計算ができるようになっているか確認します。</br>
                ファイル名: app/Models/Attendance.php</br>
                ※特に修正は不要ですが、work_time や overtime が DB 上で decimal や float 型になっていることが前提です。</br>
                PHP
            </p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;?php

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

        <li>構築のロードマップ
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
        <li>データベース設計 (既存テーブルの活用)
            <p>
                データを保存するテーブルを作成します。</br>
                （既に attendances テーブルがあるのであれば、本作業は不要です。）</br>
                ファイル名: database/migrations/xxxx_xx_xx_create_attendances_table.php</br>
                PHP
            </p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>public function up()
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

        <li>司令塔 (Controller)
            <p>
                勤務表の承認に特化したメソッドを AttendanceController に追加します。</br>
                ファイル名: app/Http/Controllers/AttendanceController.php</br>
                PHP
            </p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;?php

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

        <li>URLの設定 (Routes)
            <p>
                管理者がアクセスする専用のURLを定義します。</br>
                LeaveApplicationController のメソッドと紐付けます。</br>
                ファイル名: routes/web.php</br>
                PHP
            </p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>Route::middleware(['auth', 'can:admin'])->group(function () {
    // 休暇申請：一覧表示
    Route::get('/admin/leave-requests', [LeaveApplicationController::class, 'adminIndex'])->name('admin.leave_requests');
    
    // 承認・却下アクション
    Route::post('/admin/leave-requests/{leaveApplication}/approve', [LeaveApplicationController::class, 'approve'])->name('admin.leave_approve');
    Route::post('/admin/leave-requests/{leaveApplication}/reject', [LeaveApplicationController::class, 'reject'])->name('admin.leave_reject');
});</code></pre>
        </li>

        <li>管理者用承認画面 (View)
            <p>
                申請内容を一目で確認し、ワンクリックで承認できる画面を作成します。</br>
                ファイル名: resources/views/admin/attendance_approve.blade.php</br>
                以下の内容でファイルを新規作成、または上書き保存してください。</br>
                HTML
            </p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;!DOCTYPE html&gt;
&lt;html lang="ja"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;休暇・残業申請承認&lt;/title&gt;
    &lt;style&gt;
        body { font-family: sans-serif; background-color: #f4f7f6; padding: 20px; }
        .container { max-width: 1100px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { border-left: 5px solid #ff9800; padding-left: 15px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; color: #666; white-space: nowrap; }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; font-size: 14px; }
        .btn-approve { background-color: #4caf50; color: white; margin-right: 5px; }
        .btn-approve:hover { background-color: #43a047; }
        .btn-reject { background-color: #f44336; color: white; }
        .btn-reject:hover { background-color: #e53935; }
        .btn-back { background-color: #6c757d; color: white; margin-bottom: 20px; }
        .btn-back:hover { background-color: #5a6268; }
        .reason-text { font-size: 0.9em; color: #666; font-style: italic; max-width: 250px; word-wrap: break-word; }
        .empty-msg { text-align: center; padding: 40px; color: #999; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .emp-code { font-family: monospace; color: #666; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;div class="container"&gt;
        &lt;a href="&#123;&#123; route('admin.menu') &#125;&#125;" class="btn btn-back"&gt;← 管理メニューへ戻る&lt;/a&gt;
        
        &lt;h2&gt;休暇・残業 承認待ち一覧&lt;/h2&gt;

        &#64;if(session('success'))
            &lt;div class="alert alert-success"&gt;&#123;&#123; session('success') &#125;&#125;&lt;/div&gt;
        &#64;endif

        &#64;if($applications->isEmpty())
            &lt;div class="empty-msg"&gt;現在、未処理の申請はありません。&lt;/div&gt;
        &#64;else
            &lt;table&gt;
                &lt;thead&gt;
                    &lt;tr&gt;
                        &lt;th&gt;申請日時&lt;/th&gt;
                        &lt;th&gt;社員番号&lt;/th&gt;
                        &lt;th&gt;氏名&lt;/th&gt;
                        &lt;th&gt;対象日&lt;/th&gt;
                        &lt;th&gt;種別&lt;/th&gt;
                        &lt;th&gt;日数/時間&lt;/th&gt;
                        &lt;th&gt;理由&lt;/th&gt;
                        &lt;th&gt;操作&lt;/th&gt;
                    &lt;/tr&gt;
                &lt;/thead&gt;
                &lt;tbody&gt;
                    &#64;foreach($applications as $app)
                    &lt;tr&gt;
                        &lt;td&gt;&#123;&#123; $app->created_at->format('m/d H:i') &#125;&#125;&lt;/td&gt;
                        &lt;td class="emp-code"&gt;&#123;&#123; $app->user->detail->employee_code ?? '未設定' &#125;&#125;&lt;/td&gt;
                        &lt;td&gt;&lt;strong&gt;&#123;&#123; $app->user->detail->name_kanji ?? $app->user->name &#125;&#125;&lt;/strong&gt;&lt;/td&gt;
                        &lt;td&gt;&#123;&#123; $app->target_date &#125;&#125;&lt;/td&gt;
                        &lt;td&gt;
                            &lt;span style="padding: 2px 6px; border-radius: 4px; background: #fff3e0; color: #e65100; font-weight: bold; font-size: 0.9em;"&gt;
                                &#123;&#123; $app->type &#125;&#125;
                            &lt;/span&gt;
                        &lt;/td&gt;
                        &lt;td&gt;&#123;&#123; $app->days_count &#125;&#125;&lt;/td&gt;
                        &lt;td class="reason-text"&gt;&#123;&#123; $app->reason ?? '-' &#125;&#125;&lt;/td&gt;
                        &lt;td&gt;
                            &lt;div style="display: flex;"&gt;
                                &lt;form action="&#123;&#123; route('admin.leave_approve', $app->id) &#125;&#125;" method="POST" onsubmit="return confirm('この申請を承認し、勤務表に反映しますか？')"&gt;
                                    &#64;csrf
                                    &lt;button type="submit" class="btn btn-approve"&gt;承認&lt;/button&gt;
                                &lt;/form&gt;
                                
                                &lt;form action="&#123;&#123; route('admin.leave_reject', $app->id) &#125;&#125;" method="POST" onsubmit="return confirm('この申請を却下しますか？')"&gt;
                                    &#64;csrf
                                    &lt;button type="submit" class="btn btn-reject"&gt;却下&lt;/button&gt;
                                &lt;/form&gt;
                            &lt;/div&gt;
                        &lt;/td&gt;
                    &lt;/tr&gt;
                    &#64;endforeach
                &lt;/tbody&gt;
            &lt;/table&gt;
        &#64;endif
    &lt;/div&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
        </li>

        <li>構築の手順まとめ</li>
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
        <li>司令塔 (Controller)
            <P>既に作成した ExpenseController.php に、管理者用のメソッドを追加・整理します。</br>
                ファイル名: app/Http/Controllers/ExpenseController.php</br>
                PHP
            </P>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * 管理者専用：承認待ち経費の一覧表示
     */
    public function adminIndex()
    {
        // 申請中(status=1)、承認済(2)、却下(3)のデータをユーザー情報と一緒に取得
        $adminExpenses = Expense::with(['user.detail'])
            ->where('status', '>=', 1) 
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('expenses_admin', compact('adminExpenses'));
    }

    /**
     * ステータス更新（承認・却下）
     */
    public function updateStatus(Request $request, Expense $expense)
    {
        // 管理者権限チェック（ゲートやミドルウェアでも可）
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => '権限がありません'], 403);
        }

        $newStatus = (int)$request->status;
        $data = ['status' => $newStatus];

        // 却下（status: 3）の場合、却下理由を保存
        if ($newStatus === 3) {
            $data['reject_reason'] = $request->reject_reason;
        } else {
            $data['reject_reason'] = null;
        }

        $expense->update($data);

        return response()->json(['message' => 'ステータスを更新しました']);
    }
}</code></pre>
        </li>

        <li>URLの設定 (Routes)
            <p>
                管理者専用のURLを定義します。</bk>
                ファイル名: routes/web.php</bk>
                PHP
            </p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>use App\Http\Controllers\ExpenseController;

Route::middleware(['auth', 'admin'])->group(function () {
    // 管理者用の一覧画面
    Route::get('/expenses/admin', [ExpenseController::class, 'adminIndex'])->name('expenses.admin');
    
    // 承認・却下実行（PATCHメソッドを使用）
    Route::patch('/expenses/{expense}/status', [ExpenseController::class, 'updateStatus'])->name('expenses.updateStatus');
});</code></pre>
        </li>

        <li>管理者用画面 (View)
            <p>
                承認ボタンや却下理由入力フォームを備えた専用画面を作成します。</br>
                ファイル名: resources/views/expenses_admin.blade.php</br>
                以下の内容を新しいデザインとして保存します。</br>
                HTML
            </p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;!DOCTYPE html&gt;
&lt;html lang="ja"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;meta name="csrf-token" content="&#123;&#123; csrf_token() &#125;&#125;"&gt;
    &lt;title&gt;管理者専用 - 経費承認パネル&lt;/title&gt;
    &lt;link rel="stylesheet" href="&#123;&#123; asset('css/expenses.css') &#125;&#125;"&gt;
    &lt;style&gt;
        body { background-color: #fff5f5; padding: 20px; font-family: sans-serif; }
        .admin-container { max-width: 1400px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 2px solid darkred; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid darkred; padding-bottom: 15px; margin-bottom: 20px; }
        .back-btn { background-color: #666; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; }
        .back-btn:hover { background-color: #444; }
        .search-area { margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; display: flex; align-items: center; gap: 10px; }
        .search-input { width: 350px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .admin-table th, .admin-table td { border: 1px solid #ccc; padding: 12px 8px; text-align: center; font-size: 13px; }
        .admin-table th { background-color: #ffebee; color: darkred; white-space: nowrap; }
        .expense-row:hover { background-color: #fffde7; }
        .status-btn { padding: 6px 14px; cursor: pointer; border-radius: 4px; border: none; font-weight: bold; margin: 2px; transition: 0.2s; }
        .btn-approve { background-color: #4CAF50; color: white; }
        .btn-approve:hover { background-color: #388E3C; }
        .btn-reject { background-color: #f44336; color: white; }
        .btn-reject:hover { background-color: #d32f2f; }
        .approved { color: #2e7d32; font-weight: bold; }
        .rejected { color: #d32f2f; font-weight: bold; }
        .reason-text { font-size: 11px; color: #d32f2f; display: block; margin-top: 5px; text-align: left; background: #fff0f0; padding: 4px; border-radius: 2px; }
        .text-right { text-align: right; padding-right: 15px !important; }
        .text-bold { font-weight: bold; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;

&lt;div class="admin-container"&gt;
    &lt;div class="admin-header"&gt;
        &lt;div style="display: flex; gap: 20px; align-items: center;"&gt;
            &lt;a href="&#123;&#123; route('admin.menu') &#125;&#125;" class="back-btn" style="text-decoration: none;"&gt;
                ← 管理者専用メニューに戻る
            &lt;/a&gt;
            &lt;h2 style="color: darkred; margin: 0;"&gt;【管理者専用】経費承認パネル&lt;/h2&gt;
        &lt;/div&gt;
        &lt;button onclick="location.reload()" style="padding: 8px 20px; cursor: pointer; border-radius: 4px; background: white; border: 1px solid #ccc;"&gt;最新の情報に更新&lt;/button&gt;
    &lt;/div&gt;

    &lt;div class="search-area"&gt;
        &lt;span style="font-weight: bold; color: #444;"&gt;絞り込み抽出:&lt;/span&gt;
        &lt;input type="text" id="adminSearchInput" onkeyup="filterAdminTable()" placeholder="社員番号 または 氏名で検索..." class="search-input"&gt;
        &lt;span style="font-size: 12px; color: #888;"&gt;※入力すると即座にリストが更新されます&lt;/span&gt;
    &lt;/div&gt;

    &lt;table class="admin-table" id="adminExpenseTable"&gt;
        &lt;thead&gt;
            &lt;tr&gt;
                &lt;th style="width: 150px;"&gt;申請処理&lt;/th&gt;
                &lt;th&gt;社員番号&lt;/th&gt;
                &lt;th&gt;氏名&lt;/th&gt;
                &lt;th&gt;発生日&lt;/th&gt;
                &lt;th&gt;種別 / 詳細&lt;/th&gt;
                &lt;th&gt;出発地 / 目的地&lt;/th&gt;
                &lt;th&gt;支払先&lt;/th&gt;
                &lt;th class="text-right"&gt;金額&lt;/th&gt;
                &lt;th class="text-right"&gt;請求額&lt;/th&gt;
                &lt;th&gt;領収書&lt;/th&gt;
            &lt;/tr&gt;
        &lt;/thead&gt;
        &lt;tbody&gt;
            &#64;forelse($adminExpenses as $ae)
            &lt;tr class="expense-row"&gt;
                &lt;td&gt;
                    &#64;if($ae-&gt;status == 1)
                        &lt;button class="status-btn btn-approve" onclick="adminUpdateStatus(&#123;&#123; $ae-&gt;id &#125;&#125;, 2)"&gt;承認&lt;/button&gt;
                        &lt;button class="status-btn btn-reject" onclick="adminUpdateStatus(&#123;&#123; $ae-&gt;id &#125;&#125;, 3)"&gt;却下&lt;/button&gt;
                    &#64;elseif($ae-&gt;status == 2)
                        &lt;span class="approved"&gt;承認済み&lt;/span&gt;
                    &#64;elseif($ae-&gt;status == 3)
                        &lt;span class="rejected"&gt;却下済み&lt;/span&gt;
                        &#64;if($ae-&gt;reject_reason)
                            &lt;span class="reason-text"&gt;理由: &#123;&#123; $ae-&gt;reject_reason &#125;&#125;&lt;/span&gt;
                        &#64;endif
                    &#64;endif
                &lt;/td&gt;
                &lt;td class="search-target-id"&gt;&#123;&#123; $ae-&gt;user-&gt;detail-&gt;employee_code ?? '未設定' &#125;&#125;&lt;/td&gt;
                &lt;td class="search-target-name" style="font-weight: bold;"&gt;&#123;&#123; $ae-&gt;user-&gt;detail-&gt;name_kanji ?? ($ae-&gt;user-&gt;name ?? '不明') &#125;&#125;&lt;/td&gt;
                &lt;td&gt;&#123;&#123; $ae-&gt;occured_date &#125;&#125;&lt;/td&gt;
                &lt;td&gt;&#123;&#123; $ae-&gt;category &#125;&#125;&lt;br&gt;&lt;small style="color:#666;"&gt;&#123;&#123; $ae-&gt;subcategory &#125;&#125;&lt;/small&gt;&lt;/td&gt;
                &lt;td&gt;&#123;&#123; $ae-&gt;departure ?? '-' &#125;&#125;&lt;br&gt;→ &#123;&#123; $ae-&gt;destination ?? '-' &#125;&#125;&lt;/td&gt;
                &lt;td&gt;&#123;&#123; $ae-&gt;payee &#125;&#125;&lt;/td&gt;
                &lt;td class="text-right"&gt;&#123;&#123; number_format($ae-&gt;amount) &#125;&#125;&lt;/td&gt;
                &lt;td class="text-right text-bold" style="color: #d32f2f;"&gt;&#123;&#123; number_format($ae-&gt;claim_amount) &#125;&#125;&lt;/td&gt;
                &lt;td&gt;
                    &lt;span style="color: &#123;&#123; $ae-&gt;receipt === 'あり' ? 'green' : '#999' &#125;&#125;;"&gt;
                        &#123;&#123; $ae-&gt;receipt &#125;&#125;
                    &lt;/span&gt;
                &lt;/td&gt;
            &lt;/tr&gt;
            &#64;empty
            &lt;tr&gt;
                &lt;td colspan="10" style="padding: 50px; color: #999; font-size: 16px;"&gt;
                    現在、未処理の申請データはありません。
                &lt;/td&gt;
            &lt;/tr&gt;
            &#64;endforelse
        &lt;/tbody&gt;
    &lt;/table&gt;
&lt;/div&gt;

&lt;script&gt;
    function filterAdminTable() {
        const input = document.getElementById('adminSearchInput');
        const filter = input.value.toLowerCase();
        const rows = document.getElementsByClassName('expense-row');
        for (let i = 0; i &lt; rows.length; i++) {
            const empIdElement = rows[i].querySelector('.search-target-id');
            const empNameElement = rows[i].querySelector('.search-target-name');
            if (empIdElement && empNameElement) {
                const empId = empIdElement.textContent.toLowerCase();
                const empName = empNameElement.textContent.toLowerCase();
                if (empId.includes(filter) || empName.includes(filter)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    }
    async function adminUpdateStatus(id, status) {
        let rejectReason = null;
        if (status === 3) {
            rejectReason = prompt('却下理由を入力してください（必須）：');
            if (rejectReason === null) return;
            if (rejectReason.trim() === "") {
                alert('却下には理由の入力が必要です。');
                return;
            }
        } else {
            if (!confirm('この経費申請を承認してもよろしいですか？')) return;
        }
        try {
            const response = await fetch(`/expenses/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: status,
                    reject_reason: rejectReason
                })
            });
            const result = await response.json();
            if (response.ok) {
                alert(status === 2 ? '承認が完了しました。' : '申請を却下しました。');
                location.reload();
            } else {
                alert('エラーが発生しました: ' + (result.error || result.message || 'サーバーエラー'));
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            alert('通信に失敗しました。');
        }
    }
&lt;/script&gt;

&lt;/body&gt;
&lt;/html&gt;</code></pre>
        </li>

        <li>モデルのリレーション確認 (Model)
            <p>既に作成済みかと思いますが、Expense モデルが User を参照できるように設定されている必要があります。</br>
                ファイル名: app/Models/Expense.php</br>
                PHP
            </p>

        </li>

        <li>.構築のロードマップ</li>
        <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>public function user()
{
    return $this->belongsTo(User::class);
}</code></pre>

        <li>構築のロードマップ
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
        <li>リンク先（ルーティング）の定義
            <p>
                まず、/manual というURLが叩かれたときにPDFファイルをブラウザで開く処理を定義します。</br>
                ファイル名: routes/web.php</br>
                PHP
            </P>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>// 管理者権限（can:admin）グループの中に記述
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

        <li>メニュー画面へのボタン追加
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
                    <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&#123;&#123;-- 操作マニュアルカード --&#125;&#125;
&lt;a href="&#123;&#123; route('manual.show') &#125;&#125;" target="_blank" class="menu-card manual-card"&gt;
    &lt;span class="icon"&gt;📖&lt;/span&gt;
    &lt;span class="title"&gt;操作マニュアル&lt;/span&gt;
    &lt;p class="desc"&gt;システムの操作図解（PDF）を表示します。別タブで開きます。&lt;/p&gt;
&lt;/a&gt;</code></pre>
                </li>
            </ol>
        </li>

        <li>ボタンの構成要素と役割
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
            ファイルの配置場所（重要）
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
        <li>サーバー側：ルーティングの設定</br>
            <p>
                ブラウザから /guidebook にアクセスがあった際、Reactコンポーネントを呼び出すように設定します。</br>
                ファイル名: routes/web.php</br>
                既存の view('guidebook') を Inertia::render に書き換えるのが最大のポイントです。</br>
                PHP
            </p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>use Inertia\Inertia;

// ...（他のルート）

Route::middleware(['auth', 'verified', 'can:admin'])->group(function () {
    // Reactコンポーネント「GuideView」を呼び出す
    Route::get('/guidebook', function () {
        return Inertia::render('GuideView');
    })->name('guidebook.show');
});</code></pre>
        </li>

        <li>フロント側：PDFビューワーコンポーネントの作成</br>
            <p>
                目次、ページ送り、PDF表示の実体となるファイルを作成します。</br>
                ファイル名: resources/js/Pages/GuideView.jsx</br>
                react-pdf ライブラリを使用して、PDFの制御を行います。</br>
                JavaScript
            </p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>
import React, &#123; useState &#125; from 'react';
import &#123; Document, Page, pdfjs &#125; from 'react-pdf';

// Workerの設定（描画エンジンの読み込み）
pdfjs.GlobalWorkerOptions.workerSrc = `//unpkg.com/pdfjs-dist@$&#123;pdfjs.version&#125;/build/pdf.worker.min.mjs`;

const GuideView = () =&gt; &#123;
    const [numPages, setNumPages] = useState(null);
    const [pageNumber, setPageNumber] = useState(1);

    // 目次データ（タイトルと対応するページ番号）
    const tableOfContents = [
        &#123; title: "1. 概要", page: 1 &#125;,
        &#123; title: "2. 目次", page: 2 &#125;,
        &#123; title: "3. 実装図", page: 4 &#125;,
        &#123; title: "4. 要件定義", page: 5 &#125;,
        // ...必要に応じて追加
    ];

    function onDocumentLoadSuccess(&#123; numPages &#125;) &#123;
        setNumPages(numPages);
    &#125;

    // ページ移動関数
    const goToPage = (p) =&gt; &#123;
        if (p &gt;= 1 && p &lt;= numPages) setPageNumber(p);
    &#125;;

    return (
        &lt;div style=&#123;&#123; display: 'flex', gap: '20px', padding: '20px' &#125;&#125;&gt;
            &#123;/* ★ ブラウザのタブ名を指定 */&#125; 
            &lt;Head title="構築ガイド" /&gt;
            &lt;div style=&#123;&#123; display: 'flex', gap: '20px', padding: '20px' &#125;&#125;&gt;
            &lt;/div&gt;
            &#123;/* 左側：目次エリア */&#125;
            &lt;div style=&#123;&#123; width: '250px', borderRight: '1px solid #ccc' &#125;&#125;&gt;
                &lt;h3&gt;目次&lt;/h3&gt;
                &lt;ul style=&#123;&#123; listStyle: 'none', padding: 0 &#125;&#125;&gt;
                    &#123;tableOfContents.map((item, index) =&gt; (
                        &lt;li key=&#123;index&#125; style=&#123;&#123; marginBottom: '10px' &#125;&#125;&gt;
                            &lt;button onClick=&#123;() =&gt; goToPage(item.page)&#125; style=&#123;&#123; color: 'blue', cursor: 'pointer' &#125;&#125;&gt;
                                &#123;item.title&#125; (&#123;item.page&#125;P)
                            &lt;/button&gt;
                        &lt;/li&gt;
                    ))&#125;
                &lt;/ul&gt;
            &lt;/div&gt;

            &#123;/* 右側：PDF表示・操作エリア */&#125;
            &lt;div&gt;
                &lt;div style=&#123;&#123; marginBottom: '10px' &#125;&#125;&gt;
                    &lt;button onClick=&#123;() =&gt; goToPage(pageNumber - 1)&#125; disabled=&#123;pageNumber &lt;= 1&#125;&gt;前へ&lt;/button&gt;
                    &lt;span style=&#123;&#123; margin: '0 15px' &#125;&#125;&gt;&#123;pageNumber&#125; / &#123;numPages&#125;&lt;/span&gt;
                    &lt;button onClick=&#123;() =&gt; goToPage(pageNumber + 1)&#125; disabled=&#123;pageNumber &gt;= numPages&#125;&gt;次へ&lt;/button&gt;
                &lt;/div&gt;

                &lt;div style=&#123;&#123; border: '1px solid #000' &#125;&#125;&gt;
                    &lt;Document file="/storage/guidebook.pdf" onLoadSuccess=&#123;onDocumentLoadSuccess&#125;&gt;
                        &lt;Page pageNumber=&#123;pageNumber&#125; width=&#123;700&#125; renderTextLayer=&#123;false&#125; renderAnnotationLayer=&#123;false&#125; /&gt;
                    &lt;/Document&gt;
                &lt;/div&gt;
            &lt;/div&gt;
        &lt;/div&gt;
    );
&#125;;

export default GuideView;</code></pre>
        </li>

        <li>PDFファイルの配置</br>
            <p>Reactが読み込める場所にPDFファイルを置きます。</p>
            <ol>
                <li><strong>物理的な配置: </strong>storage/app/public/guidebook.pdf にファイルを保存します。</li>
                <li><strong>公開設定: </strong>以下のコマンドを実行して、ブラウザから見えるようにします。</br>
                    <p>
                        Bash</br>
                        <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code><pre><code>php artisan storage:link</code></pre>
                        ※ これにより public/storage/guidebook.pdf というパスでアクセス可能になります。
                    </p>
                </li>
            </ol>
        </li>

        <li>ビルドと実行</br>
            <p>
                最後に、作成したJSXファイルをJavaScriptにコンパイルします。</br>
                <ol>
                    <li>イブラリ未導入の場合:</br>
                        Bash</br>
                        <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>npm install react-pdf --legacy-peer-deps</code></pre>
                    </li>

                    <li>ビルドの実行:</br>
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