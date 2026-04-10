<section>
    <h2 id="users">5. 管理者および一般ユーザー用ページの作成</h2>
    <h3 id="Profile">5-1. プロフィール（ユーザー詳細情報）</h3>
        <P>ロフィールの詳細表示・編集画面（user_details/show.blade.php）を、x-app-layout を使わずに、</br>
            管理者と一般社員の両方が利用できる統一デザインで作成する方法をまとめます。</br>
            この画面は、社員番号、入社日、住所、電話番号などの重要な個人情報を管理する場所です。
        </P>
        <ol>
            <li>ビューファイルの作成</br>
                <p>
                    以下の内容を新しいデザインとして保存します。</br>
                    保存パス: resources/views/user_details/show.blade.php</br>
                    HTML
                </p>
                <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;!DOCTYPE html&gt;
&lt;html lang="ja"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;ユーザー詳細情報 | 業務管理システム&lt;/title&gt;
    &lt;style&gt;
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; color: #333; }
        .container { background: white; padding: 40px; border-radius: 12px; shadow: 0 4px 12px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #1a73e8; padding-bottom: 15px; margin-bottom: 30px; }
        .title { font-size: 1.6em; font-weight: bold; margin: 0; color: #1a73e8; }
        .nav-links a { color: #666; text-decoration: none; font-size: 0.9em; margin-left: 15px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #555; font-size: 0.9em; }
        input[type="text"], input[type="email"], input[type="password"], input[type="date"], select {
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 1em;
        }
        input:disabled { background-color: #f9f9f9; color: #888; cursor: not-allowed; }
        .admin-section { background-color: #f8fbff; border: 1px solid #d1e3ff; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .admin-label { color: #1a73e8; font-size: 0.8em; font-weight: bold; margin-bottom: 10px; display: block; }
        .btn-update { background-color: #1a73e8; color: white; padding: 14px 28px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; font-size: 1.1em; transition: 0.3s; }
        .btn-update:hover { background-color: #1557b0; }
        .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;

&lt;div class="container"&gt;
    &lt;div class="header-flex"&gt;
        &lt;h1 class="title"&gt;👤 ユーザー詳細情報&lt;/h1&gt;
        &lt;div class="nav-links"&gt;
            &#64;if(Auth::user()-&gt;is_admin)
                &lt;a href="&#123;&#123; route('admin.users.index') &#125;&#125;"&gt;社員名簿へ戻る&lt;/a&gt;
            &#64;endif
            &lt;a href="&#123;&#123; route('dashboard') &#125;&#125;"&gt;ダッシュボード&lt;/a&gt;
        &lt;/div&gt;
    &lt;/div&gt;

    &#64;if(session('success'))
        &lt;div class="alert-success"&gt;&#123;&#123; session('success') &#125;&#125;&lt;/div&gt;
    &#64;endif

    &lt;form action="&#123;&#123; route('user_details.update', $user) &#125;&#125;" method="POST"&gt;
        &#64;csrf
        &#64;method('PATCH')

        &lt;div class="admin-section"&gt;
            &lt;span class="admin-label"&gt;※管理者専用設定項目&lt;/span&gt;
            &lt;div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;"&gt;
                &lt;div class="form-group"&gt;
                    &lt;label&gt;社員番号&lt;/label&gt;
                    &lt;input type="text" name="employee_code" value="&#123;&#123; old('employee_code', $user-&gt;detail-&gt;employee_code ?? '') &#125;&#125;" &#123;&#123; Auth::user()-&gt;is_admin ? '' : 'disabled' &#125;&#125;&gt;
                &lt;/div&gt;
                &lt;div class="form-group"&gt;
                    &lt;label&gt;入社年月日&lt;/label&gt;
                    &lt;input type="date" name="joined_at" value="&#123;&#123; old('joined_at', $user-&gt;detail-&gt;joined_at ?? '') &#125;&#125;" &#123;&#123; Auth::user()-&gt;is_admin ? '' : 'disabled' &#125;&#125;&gt;
                &lt;/div&gt;
            &lt;/div&gt;
            &lt;div class="form-group"&gt;
                &lt;label&gt;氏名（漢字）&lt;/label&gt;
                &lt;input type="text" name="name_kanji" value="&#123;&#123; old('name_kanji', $user-&gt;detail-&gt;name_kanji ?? '') &#125;&#125;" &#123;&#123; Auth::user()-&gt;is_admin ? '' : 'disabled' &#125;&#125;&gt;
            &lt;/div&gt;
            &#64;if(Auth::user()-&gt;is_admin && Auth::id() !== $user-&gt;id)
            &lt;div class="form-group"&gt;
                &lt;label&gt;管理者権限&lt;/label&gt;
                &lt;select name="is_admin"&gt;
                    &lt;option value="0" &#123;&#123; !$user-&gt;is_admin ? 'selected' : '' &#125;&#125;&gt;一般社員&lt;/option&gt;
                    &lt;option value="1" &#123;&#123; $user-&gt;is_admin ? 'selected' : '' &#125;&#125;&gt;管理者&lt;/option&gt;
                &lt;/select&gt;
            &lt;/div&gt;
            &#64;endif
        &lt;/div&gt;

        &lt;div class="form-group"&gt;
            &lt;label&gt;メールアドレス&lt;/label&gt;
            &lt;input type="email" name="email" value="&#123;&#123; old('email', $user-&gt;email) &#125;&#125;" required&gt;
        &lt;/div&gt;

        &lt;div class="form-group"&gt;
            &lt;label&gt;現住所（市区町村以降）&lt;/label&gt;
            &lt;input type="text" name="address_city" value="&#123;&#123; old('address_city', $user-&gt;detail-&gt;address_city ?? '') &#125;&#125;" required&gt;
        &lt;/div&gt;

        &lt;div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;"&gt;
            &lt;div class="form-group"&gt;
                &lt;label&gt;電話番号&lt;/label&gt;
                &lt;input type="text" name="phone_number" value="&#123;&#123; old('phone_number', $user-&gt;detail-&gt;phone_number ?? '') &#125;&#125;" required&gt;
            &lt;/div>
            &lt;div class="form-group"&gt;
                &lt;label&gt;緊急連絡先&lt;/label&gt;
                &lt;input type="text" name="emergency_contact" value="&#123;&#123; old('emergency_contact', $user-&gt;detail-&gt;emergency_contact ?? '') &#125;&#125;" required&gt;
            &lt;/div&gt;
        &lt;/div&gt;

        &lt;hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;"&gt;

        &lt;div class="form-group"&gt;
            &lt;label&gt;新しいパスワード（変更する場合のみ入力）&lt;/label&gt;
            &lt;input type="password" name="password"&gt;
        &lt;/div&gt;
        &lt;div class="form-group"&gt;
            &lt;label&gt;パスワード再入力&lt;/label&gt;
            &lt;input type="password" name="password_confirmation"&gt;
        &lt;/div&gt;

        &lt;button type="submit" class="btn-update"&gt;保存する&lt;/button&gt;
    &lt;/form&gt;
&lt;/div&gt;

&lt;/body&gt;
&lt;/html&gt;</code></pre>
            </li>

            <li>ルーティングの定義 (routes/web.php)</br>
                <p>
                    UserDetailController の show と update メソッドに紐付けます。</br>
                    PHP
                </p>
                <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>Route::middleware(['auth'])->group(function () {
    // ユーザー詳細情報の表示
    Route::get('/user-details/{user}', [UserDetailController::class, 'show'])->name('user_details.show');
    
    // 更新処理
    Route::patch('/user-details/{user}', [UserDetailController::class, 'update'])->name('user_details.update');
});</code></pre>
            </li>

            <li>反映の手順</br>
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
            <li>データベース設計図 (Migration)</br>
                <p>
                    データを保存するテーブルを作成します。</br>
                    （既に attendances テーブルがあるのであれば、本作業は不要です。）</br>
                    ファイル名: database/migrations/xxxx_xx_xx_create_attendances_table.php</br>
                    PHP
                </p>
                <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>public function up()
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

            <li>データの窓口 (Model)</br>
                <p>プログラムからテーブルを操作するための設定です。</br>
                    ファイル名: app/Models/Attendance.php</br>
                    php
                </p>
                <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;?php

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

            <li>司令塔 (Controller)</br>
                <p>
                    画面表示と保存のロジックを担当します。</br>
                    ファイル名: app/Http/Controllers/AttendanceController.php</br>
                    PHP
                </p>
                <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;php

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

            <li>URLの設定 (Routes)</br>
                <p>ブラウザのアドレスとコントローラーを紐付けます。</br>
                    ファイル名: routes/web.php</br>
                    PHP
                </p>
                <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>use App\Http\Controllers\AttendanceController;

Route::middleware(['auth'])->group(function () {
    // 勤務表メイン
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    
    // 基本情報の非同期保存 (JSから呼ばれる)
    Route::post('/attendance/update-basic', [AttendanceController::class, 'updateBasicInfo'])
        ->name('attendance.updateBasicInfo');
});</code></pre>
            </li>

            <li>Viewファイルの作成</br>
                <p>
                    以下の内容を新しいデザインとして保存します。</br>
                    x-app-layout を使わず、独自のスタイルで構築します。</br>
                    ファイル名：resources/views/attendance.blade.php</br>
                    PHP
                </p>
                <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code><pre>&lt;!DOCTYPE html&gt;
&lt;html lang="ja"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;meta name="csrf-token" content="&#123;&#123; csrf_token() &#125;&#125;"&gt;
    &lt;title&gt;勤務表 - &#123;&#123; $targetUser-&gt;detail-&gt;name_kanji ?? $targetUser-&gt;name &#125;&#125;&lt;/title&gt;
    &lt;link rel="stylesheet" href="&#123;&#123; asset('css/Attendance.css') &#125;&#125;"&gt;
    &lt;style&gt;
        body { background-image: url("&#123;&#123; asset('/png/favicon_1.png') &#125;&#125;"); }
        .sunday, .holiday { background-color: #ffebee !important; }
        .saturday { background-color: #e3f2fd !important; }
        .logout-button { background: none; border: none; color: blue; text-decoration: underline; cursor: pointer; padding: 0; }
        .header-container { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background: rgba(255,255,255,0.8); border-bottom: 1px solid #ddd; }
        .header-left { display: flex; gap: 10px; align-items: center; }
        .month-nav-container { display: flex; justify-content: center; align-items: center; gap: 20px; margin: 20px 0; }
        .month-nav-btn { padding: 8px 16px; background-color: #1976d2; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-header { display: inline-block; padding: 8px 15px; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 0.9em; }
        .btn-profile { background-color: #4caf50; }
        .btn-expense { background-color: #007bff; }
        .btn-guide { background-color: #9c27b0; }
        .btn-leave { background-color: #ff9800; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        #emp { margin-left: 20px; background: white; padding: 15px; border-radius: 8px; display: inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.1); min-width: 950px; }
        .btn-confirm-basic { background-color: #17a2b8; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; margin-left: 10px; vertical-align: middle; }
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fff; margin: 10% auto; padding: 20px; border-radius: 8px; width: 450px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        .paid-remaining-val { font-size: 1.2em; color: #d32f2f; font-weight: bold; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &#64;php
        $safeAttendances = $attendances ?? [];
        $safeHolidays = $holidays ?? [];
        $safeTargetMonth = $targetMonth ?? date('Y-m');
        $existingApps = [];
        try {
            $existingApps = \DB::table('leave_applications')
                -&gt;where('user_id', $targetUser-&gt;id)
                -&gt;whereIn('status', [0, 1])
                -&gt;pluck('target_date')
                -&gt;map(fn($d) =&gt; \Carbon\Carbon::parse($d)-&gt;format('Y-m-d'))
                -&gt;toArray();
        } catch (\Exception $e) {
            Log::error("Leave App Fetch Error: " . $e-&gt;getMessage());
        }
    &#64;endphp

    &lt;div class="header-container"&gt;
        &lt;div class="header-left"&gt;
            &lt;a href="&#123;&#123; route('user_details.show', Auth::id()) &#125;&#125;" class="btn-header btn-profile"&gt;👤 プロフィール&lt;/a&gt;
            &lt;a href="&#123;&#123; route('expenses.index') &#125;&#125;" class="btn-header btn-expense"&gt;💰 経費精算&lt;/a&gt;
            &lt;a href="&#123;&#123; route('guide.show') &#125;&#125;" class="btn-header btn-guide" style="margin-left: 5px;"&gt;📖 操作ガイド&lt;/a&gt;
        &lt;/div&gt;
        &lt;div&gt;
            &#64;if(Auth::user()-&gt;is_admin)
                &lt;a href="&#123;&#123; route('admin.menu') &#125;&#125;" style="margin-right: 15px; font-weight: bold; color: #1976d2; text-decoration: none;"&gt;⚙ 管理メニュー&lt;/a&gt;
            &#64;endif
            &lt;form method="POST" action="&#123;&#123; route('logout') &#125;&#125;" style="display: inline;"&gt;
                &#64;csrf
                &lt;button type="submit" class="logout-button"&gt;サインアウト&lt;/button&gt;
            &lt;/form&gt;
        &lt;/div&gt;
    &lt;/div&gt;

    &lt;div class="month-nav-container"&gt;
        &lt;button id="btnPrevMonth" class="month-nav-btn"&gt;◀ 前月&lt;/button&gt;
        &lt;h2 id="title" style="margin: 0; min-width: 200px; text-align: center;"&gt;&#123;&#123; substr($safeTargetMonth, 0, 4) &#125;&#125;年&#123;&#123; substr($safeTargetMonth, 5, 2) &#125;&#125;月 勤務表&lt;/h2&gt;
        &lt;button id="btnNextMonth" class="month-nav-btn"&gt;翌月 ▶&lt;/button&gt;
    &lt;/div&gt;

    &lt;div id="emp"&gt;
        &lt;div id="user_info"&gt;
            社員番号：&lt;input type="text" value="&#123;&#123; $targetUser-&gt;detail-&gt;employee_code ?? '未設定' &#125;&#125;" disabled style="width:100px; background-color: #eee;"&gt;
            氏名：&lt;input type="text" value="&#123;&#123; $targetUser-&gt;detail-&gt;name_kanji ?? $targetUser-&gt;name &#125;&#125;" disabled style="width:160px; background-color: #eee; font-weight: bold;"&gt;
            所属：&lt;select id="empDept"&gt;
                    &lt;option value=""&gt;未設定&lt;/option&gt;
                    &#64;foreach(['総務グループ', '営業グループ', '第１グループ', '第２グループ'] as $dept)
                        &lt;option value="&#123;&#123; $dept &#125;&#125;" &#123;&#123; (($targetUser-&gt;department ?? ($targetUser-&gt;detail-&gt;department ?? '')) == $dept) ? 'selected' : '' &#125;&#125;&gt;&#123;&#123; $dept &#125;&#125;&lt;/option&gt;
                    &#64;endforeach
                &lt;/select&gt;
            通勤費：&lt;input type="number" id="commuteCost" value="&#123;&#123; $targetUser-&gt;daily_commute_fee ?? 0 &#125;&#125;" oninput="updateLiveCommuteTotal()" style="width:70px;"&gt; 円
            &lt;button type="button" class="btn-confirm-basic" onclick="submitBasicInfo()"&gt;確定&lt;/button&gt;
            &lt;span style="margin-left:20px; font-weight: bold;"&gt;ステータス：&lt;/span&gt;
            &#64;php
                $totalDays = $attendances-&gt;count();
                $approvedCount = $attendances-&gt;where('is_locked', 2)-&gt;count();
                $pendingCount = $attendances-&gt;where('is_locked', 1)-&gt;count();
                $unregisteredCount = $attendances-&gt;where('is_locked', 0)-&gt;count();
            &#64;endphp
            &lt;span id="monthlyStatusDisplay" style="padding: 3px 10px; border-radius: 4px; border: 1px solid; font-weight: bold;"&gt;
                &#64;if($approvedCount === $totalDays && $totalDays &gt; 0) ✅ 承認済み &#64;elseif($pendingCount &gt; 0) 🚩 確定 &#64;else 未登録 &#64;endif
            &lt;/span&gt;
            &lt;span style="margin-left:25px;"&gt;有給残：&lt;/span&gt;
            &lt;span class="paid-remaining-val"&gt;&#123;&#123; number_format($targetUser-&gt;paid_leave_remaining ?? 0, 1) &#125;&#125;&lt;/span&gt; 日
        &lt;/div&gt;
    &lt;/div&gt;

    &lt;table id="workTable" style="margin: 20px auto; width: 95%;"&gt;
        &lt;thead&gt;
            &lt;tr&gt;&lt;th&gt;登録&lt;/th&gt;&lt;th&gt;日付&lt;/th&gt;&lt;th&gt;特殊&lt;/th&gt;&lt;th&gt;勤怠&lt;/th&gt;&lt;th&gt;勤務先&lt;/th&gt;&lt;th&gt;シフト&lt;/th&gt;&lt;th&gt;開始&lt;/th&gt;&lt;th&gt;終了&lt;/th&gt;&lt;th&gt;休憩&lt;/th&gt;&lt;th&gt;残業&lt;/th&gt;&lt;th&gt;稼働&lt;/th&gt;&lt;/tr&gt;
        &lt;/thead&gt;
        &lt;tbody id="tableBody"&gt;&lt;/tbody&gt;
    &lt;/table&gt;

    &lt;script&gt;
        window.displayUserId = &#123;&#123; $targetUser-&gt;id &#125;&#125;;
        window.savedDataFromServer = &#64;json($safeAttendances);
        window.isAdmin = &#123;&#123; Auth::user()-&gt;is_admin ? 'true' : 'false' &#125;&#125;;
        window.japaneseHolidays = &#64;json($safeHolidays);
        window.currentMonthStr = "&#123;&#123; $safeTargetMonth &#125;&#125;";
    &lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
            </li>

            <li>.JavaScriptファイル</br>
                <p>
                    カレンダーの動的生成や計算ロジックを記述したファイルです。</br>
                    ファイル名：public/js/Attendance.js</br>
                    JavaScript
                </p>
                <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><pre><code>/**
 * Attendance.js - 勤務表管理・申請ワークフロー 最終統合版
 */

// --- 1. グローバル定数・変数の宣言 ---
let currentYear, currentMonth, daysInMonth;

const shiftTimes = &#123;
    "常勤：09:00 - 18:00": ["09:00", "18:00", 1.0],
    "早番：06:00 - 15:00": ["06:00", "15:00", 1.0],
    "遅番：14:00 - 23:00": ["14:00", "23:00", 1.0],
    "深夜：22:00 - 07:00": ["22:00", "08:00", 2.0],
&#125;;
const attendanceTime = &#123; 有給: 8.0, 特別休暇: 8.0, 欠勤: -8.0 &#125;;

// --- 2. 初期化処理 ---
document.addEventListener("DOMContentLoaded", () =&gt; &#123;
    console.log("Attendance.js: Initializing...");

    try &#123;
        const dateStr =
            window.currentMonthStr || new Date().toISOString().slice(0, 7);
        [currentYear, currentMonth] = dateStr.split("-").map(Number);
        daysInMonth = new Date(currentYear, currentMonth, 0).getDate();

        const titleEl = document.getElementById("title");
        if (titleEl) &#123;
            titleEl.textContent = `&#36;&#123;currentYear&#125;年&#36;&#123;currentMonth.toString().padStart(2, "0")&#125;月分 勤務表`;
        &#125;
    &#125; catch (e) &#123;
        console.error("Date Initialization Error:", e);
    &#125;

    initMonthNav();
    initLeaveModal();
    loadData();
    registerLeaveValidation();
&#125;);

// --- 3. カレンダー描画ロジック ---
function loadData() &#123;
    const tableBody = document.getElementById("tableBody");
    if (!tableBody) return;

    tableBody.innerHTML = "";
    const db = window.savedDataFromServer || [];
    const map = &#123;&#125;;

    db.forEach((i) =&gt; &#123;
        if (!map[i.work_date]) map[i.work_date] = [];
        map[i.work_date].push(i);
    &#125;);

    for (let d = 1; d &lt;= daysInMonth; d++) &#123;
        const dk = `&#36;&#123;currentYear&#125;-&#36;&#123;String(currentMonth).padStart(2, "0")&#125;-&#36;&#123;String(d).padStart(2, "0")&#125;`;
        const entries = map[dk] || [null];
        entries.forEach((e) =&gt; createRow(d, e));
    &#125;
    calculateTotals();
&#125;

function createRow(day, dbEntry = null) &#123;
    const tableBody = document.getElementById("tableBody");
    const row = document.createElement("tr");
    const dateKey = `&#36;&#123;currentYear&#125;-&#36;&#123;String(currentMonth).padStart(2, "0")&#125;-&#36;&#123;String(day).padStart(2, "0")&#125;`;
    const date = new Date(currentYear, currentMonth - 1, day);
    const dayLabels = ["日", "月", "火", "水", "木", "金", "土"];
    const dayStr = `&#36;&#123;currentMonth&#125;/&#36;&#123;day&#125;(&#36;&#123;dayLabels[date.getDay()]&#125;)`;

    row.setAttribute("data-date", dateKey);

    if (
        date.getDay() === 0 ||
        (window.japaneseHolidays && window.japaneseHolidays.includes(dateKey))
    ) &#123;
        row.classList.add("sunday");
    &#125; else if (date.getDay() === 6) &#123;
        row.classList.add("saturday");
    &#125;

    const s = dbEntry ? parseInt(dbEntry.is_locked) : 0;
    const att = dbEntry ? dbEntry.attendance_type : "";
    const wp = dbEntry ? dbEntry.workplace : "社内・本社";
    const sh = dbEntry ? dbEntry.shift : "";
    const st = dbEntry ? (dbEntry.start_time ? dbEntry.start_time.substring(0, 5) : "") : "";
    const ed = dbEntry ? (dbEntry.end_time ? dbEntry.end_time.substring(0, 5) : "") : "";
    const rs = dbEntry ? (parseFloat(dbEntry.rest_time) || 0).toFixed(2) : "";
    const ot = dbEntry ? (parseFloat(dbEntry.overtime) || 0).toFixed(2) : "0.00";
    const wk = dbEntry ? (parseFloat(dbEntry.work_time) || 0).toFixed(2) : "0.00";

    const btnT = s === 3 ? "確定済み" : s === 2 ? "承認済み" : s === 1 ? "承認待ち" : "登録";

    row.innerHTML = `
        &lt;td&gt;&lt;button type="button" onclick="handleStatusChange(this)" class="status-btn"&gt;&#36;&#123;btnT&#125;&lt;/button&gt;&lt;/td&gt;
        &lt;td&gt;&#36;&#123;dayStr&#125;&lt;/td&gt;
        &lt;td&gt;&lt;button type="button" onclick="insertSpecialRow(this)" class="special-btn"&gt;特殊&lt;/button&gt;&lt;/td&gt;
        &lt;td&gt;&lt;select onchange="updateTimes(this)"&gt;
            &lt;option value=""&gt;&lt;/option&gt;
            &#36;&#123;["出勤", "欠勤", "遅刻", "早退", "午前半休", "午後半休", "有給", "特別休暇", "休日", "休出", "フレックス"].map((v) =&gt; `&lt;option &#36;&#123;att === v ? "selected" : ""&#125;&gt;&#36;&#123;v&#125;&lt;/option&gt;`).join("")&#125;
        &lt;/select&gt;&lt;/td&gt;
        &lt;td&gt;&lt;select onchange="saveData()"&gt;
            &#36;&#123;["社内・本社", "社内・支店（営業所）", "社内・テレワーク", "社外・（外勤）"].map((v) =&gt; `&lt;option &#36;&#123;wp === v ? "selected" : ""&#125;&gt;&#36;&#123;v&#125;&lt;/option&gt;`).join("")&#125;
        &lt;/select&gt;&lt;/td&gt;
        &lt;td&gt;&lt;select onchange="updateTimes(this)"&gt;
            &lt;option value=""&gt;&lt;/option&gt;&#36;&#123;Object.keys(shiftTimes).map((v) =&gt; `&lt;option &#36;&#123;sh === v ? "selected" : ""&#125;&gt;&#36;&#123;v&#125;&lt;/option&gt;`).join("")&#125;
        &lt;/select&gt;&lt;/td&gt;
        &lt;td&gt;&lt;input type="time" onchange="calcRowTime(this.closest('tr'))" value="&#36;&#123;st&#125;"&gt;&lt;/td&gt;
        &lt;td&gt;&lt;input type="time" onchange="calcRowTime(this.closest('tr'))" value="&#36;&#123;ed&#125;"&gt;&lt;/td&gt;
        &lt;td&gt;&lt;input type="text" readonly value="&#36;&#123;rs&#125;"&gt;&lt;/td&gt;
        &lt;td&gt;&lt;input type="text" readonly value="&#36;&#123;ot&#125;"&gt;&lt;/td&gt;
        &lt;td&gt;&lt;input type="text" readonly value="&#36;&#123;wk&#125;"&gt;&lt;/td&gt;
    `;
    tableBody.appendChild(row);
    applyLockState(row, s);
&#125;

// (以下、関数群も同様にエスケープ済み)

function saveData() &#123;
    const tableBody = document.getElementById("tableBody");
    if (!tableBody) return;
    const data = [];
    [...tableBody.rows].forEach((row) =&gt; &#123;
        // 保存ロジック...
    &#125;);

    fetch("/attendance/save", &#123;
        method: "POST",
        headers: &#123;
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        &#125;,
        body: JSON.stringify(&#123; attendances: data &#125;),
    &#125;);
&#125;</code></pre>
            </li>

            <li>CSSファイル<br>
                <p>
                    ファイル名：public/css/Attendance.css</br>
                    CSS
                </p>
                <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><pre><code>body &#123;
  background-image: url(./png/favicon_1.png);
  margin: 0;
  padding: 0;
  font-family: sans-serif;
&#125;

/* タイトル中央配置 */
#title &#123;
  text-align: center;
  font-size: 24px;
  margin-top: 10px;
  margin-bottom: 20px;
&#125;

/* 左上：iframeとリンク */
.top-left &#123;
  position: absolute;
  top: 10px;
  left: 10px;
  display: flex;
  flex-direction: column;
  gap: 1px;
  align-items: flex-start;
&#125;

/* 経費精算・全てクリアボタンのサイズ調整 */
.top-left button &#123;
  font-size: 20px;
  padding: 10px 10px;
&#125;

/* iframeのサイズ統一 */
.top-left &#123;
  position: absolute;
  top: 5px;
  left: 10px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  align-items: flex-start;
&#125;

.button-row &#123;
  display: flex;
  gap: 10px;
  align-items: center;
&#125;

.top-left button &#125;
  font-size: 14px;
  padding: 5px 10px;
&#125;

.top-left iframe &#123;
  border: none;
  width: 120px;
  height: 50px;
&#125;

.top-right &#123;
  position: absolute;
  top: 10px;
  right: 10px;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 5px;
&#125;

#top-link &#123;
  font-size: 16px;
&#125;

.top-right .top-link:hover &#123;
  text-decoration: underline;
&#125;

#emp &#123;
  margin-top: 50px;
  margin-right: 10px;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-bottom: 1rem;
  align-items: center;
&#125;

#watch &#123;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 20px;
  z-index: 10;
  background-color: transparent;
&#125;

table &#123;
  background-color: #f0f0f0;
  border-collapse: collapse;
  width: 100%;
  margin-top: 20px;
  position: relative;
  z-index: 1;
&#125;

th, td &#123;
  border: 1px solid #ccc;
  padding: 5px;
  text-align: center;
&#125;

input[type="time"] &#123;
  width: 80px;
&#125;

input[readonly],
select:disabled, input:disabled &#123;
  background-color: #f0f0f0;
&#125;

.special-row &#123;
  background-color: #ffe;
&#125;

.chime-frame &#123;
  padding-bottom: 0;
  display: flex;
  border: none;
  margin-top: 40px;
&#125;</code></pre>
            </li>

            <li>構築の順番（ロードマップ）</br>
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
        <li>データベース設計図 (Migration)</br>
            <p>
                経費データを保存するテーブルを作成します。</br>
                ファイル名: database/migrations/xxxx_xx_xx_create_expenses_table.php</br>
                PHP
            </p>
            <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><pre><code>public function up()
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
        
        <li>データの窓口 (Model)</br>
            <p>
                プログラムから expenses テーブルを操作するための設定です。</br>
                ファイル名: app/Models/Expense.php</br>
                PHP
            </p>
            <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><pre><code>&lt;?php

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

        <li>司令塔 (Controller)</br>
            <p>
                画面の表示、データの登録、ステータスの更新（申請・承認）を処理します。</br>
                ファイル名: app/Http/Controllers/ExpenseController.php</br>
                PHP
            </p>
            <pre style="background: #2d2d2d;color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><pre><code>&lt;?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
&#123;
    // 一般ユーザー用画面
    public function index()
    &#123;
        $user = Auth::user()-&gt;load('detail');
        $expenses = Expense::where('user_id', $user-&gt;id)-&gt;orderBy('occured_date', 'desc')-&gt;take(30)-&gt;get();
        return view('expenses', compact('expenses', 'user'));
    &#125;

    // データ保存
    public function store(Request $request)
    &#123;
        $amount = (int)$request-&gt;amount;
        $prepaid = (int)$request-&gt;prepaid;

        Expense::create([
            'user_id'      =&gt; Auth::id(),
            'occured_date' =&gt; $request-&gt;occured_date,
            'category'     =&gt; $request-&gt;category,
            'subcategory'  =&gt; $request-&gt;subcategory,
            'departure'    =&gt; $request-&gt;departure,
            'destination'  =&gt; $request-&gt;destination,
            'payee'        =&gt; $request-&gt;payee,
            'amount'       =&gt; $amount,
            'prepaid'      =&gt; $prepaid,
            'claim_amount' =&gt; $amount - $prepaid,
            'receipt'      =&gt; $request-&gt;receipt,
            'status'       =&gt; 0,
        ]);
        return response()-&gt;json(['message' =&gt; '登録しました']);
    &#125;

    // ステータス更新（申請・承認・却下）
    public function updateStatus(Request $request, Expense $expense)
    &#123;
        $expense-&gt;update([
            'status' =&gt; $request-&gt;status,
            'reject_reason' =&gt; $request-&gt;status == 3 ? $request-&gt;reject_reason : null
        ]);
        return response()-&gt;json(['message' =&gt; '更新しました']);
    &#125;

    public function adminIndex()
    &#123;
        // 全社員の経費データを取得
        $expenses = Expense::with('user')
                    -&gt;orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
                    -&gt;orderBy('created_at', 'desc')
                    -&gt;get();

        return view('admin.expenses.index', compact('expenses'));
    &#125;
&#125;</code></pre>
        </li>

        <li>
    <strong>URLの設定 (Routes)</strong><br>
    <p>
        ブラウザのアドレスとコントローラーを紐付けます。<br>
        ファイル名: <code>routes/web.php</code>
    </p>

    <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5;"><code>@verbatim Route::middleware(['auth', 'verified'])->group(function () {
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
    
    <li>View ファイルの作成</br>
    <p>
        以下の内容を新しいデザインとして保存します。</br>
        x-app-layout を使わず、独自のスタイルで構築します。</br>
        保存パス: resources/views/expenses.blade.php</br>
        HTML
    </p>
    <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5;"><code>{!! e(<<<'EOT'
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>経費精算表 | 業務管理システム</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/expenses.css') }}">
    <script src="{{ asset('js/encoding.min.js') }}"></script>
    
    <style>
        body {
            font-family: 'Inter', 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif;
            background-color: #fdf5ef;
            margin: 0;
            padding-bottom: 50px;
        }
        .header-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .main-container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .form-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 30px;
            margin-bottom: 40px;
        }
        .user-profile {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #ddd;
            color: #555;
        }
        .input-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .input-group {
            display: flex;
            flex-direction: column;
        }
        .input-group label {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: #444;
        }
        input, select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
        }
        .claim-summary {
            grid-column: 1 / -1;
            background: #f0f7ff;
            padding: 20px;
            border-radius: 8px;
            text-align: right;
            margin-top: 10px;
        }
        .claim-value { font-size: 2rem; font-weight: 700; color: #1a73e8; margin-left: 10px; }
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .badge-draft { background: #e0e0e0; color: #616161; }
        .badge-pending { background: #fff3e0; color: #ef6c00; }
        .badge-approved { background: #e8f5e9; color: #2e7d32; }
        .badge-rejected { background: #ffebee; color: #c62828; }
        .report-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .report-table th { background-color: #444; color: #fff; padding: 15px; }
        .report-table td { padding: 15px; border-bottom: 1px solid #eee; text-align: center; }
        .btn-primary { background: #1a73e8; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; }
        .btn-outline { background: white; border: 1px solid #ccc; padding: 8px 15px; border-radius: 6px; cursor: pointer; }
    </style>
</head>
<body>
    <nav class="header-nav">
        <div>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-outline">サインアウト</button>
            </form>
        </div>
    </nav>

    <div class="main-container">
        <div class="form-card">
            <div class="user-profile">
                <span><strong>氏名:</strong> {{ Auth::user()->name }}</span>
            </div>
            <div class="input-grid">
                <div class="claim-summary">
                    <span class="claim-value">¥ <span id="claimAmount">0</span></span>
                </div>
            </div>
        </div>

        <table class="report-table">
            <thead>
                <tr><th>状態</th><th>発生日</th><th>内容</th><th>金額</th><th>操作</th></tr>
            </thead>
            <tbody>
                @forelse($expenses as $ex)
                <tr>
                    <td>
                        @if($ex->status == 0) <span class="badge badge-draft">下書き</span> @endif
                    </td>
                    <td>{{ $ex->occured_date }}</td>
                    <td>{{ $ex->category }}</td>
                    <td>{{ number_format($ex->claim_amount) }}円</td>
                    <td>
                        <button onclick="deleteExpense({{ $ex->id }})">削除</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5">データがありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        async function submitData() {
            const res = await fetch("{{ route('expenses.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ /* payload */ })
            });
        }
    </script>
</body>
</html>
EOT
) !!}</code></pre>
</li>

        <li>CSSファイル</br>
            <p>ファイル名：public/css/expenses.css</br>
                CSS
            </p>
<pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5;"><code>{!! e(<<<'EOT'
/* 全体の基本設定 */
body {
    background-color: #f4f7f9; /* 落ち着いたグレーベージュに変更 */
    font-family: 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif;
    color: #333;
    margin: 0;
    padding-top: 60px; /* ヘッダー固定用 */
}

/* ヘッダー・ナビゲーション */
.top-left, .top-right {
    position: fixed;
    top: 0;
    height: 50px;
    display: flex;
    align-items: center;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 1000;
    padding: 0 20px;
}
.top-left { left: 0; width: 100%; border-bottom: 1px solid #ddd; }
.top-right { right: 20px; background: transparent; box-shadow: none; }

/* コンテナ */
.main-container {
    max-width: 1000px;
    margin: 30px auto;
    padding: 20px;
}

/* 入力セクション（カード型） */
.form-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    padding: 30px;
    margin-bottom: 40px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px 25px;
}

/* テーブルスタイル（モダン化） */
.report-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border: none;
}

.report-table th {
    background-color: #1a73e8;
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 13px;
    padding: 12px;
    border: none;
}

.report-table td {
    padding: 15px 12px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

/* ステータスバッジ */
.badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}
.badge-pending { background: #fff3e0; color: #ef6c00; }
.badge-approved { background: #e8f5e9; color: #2e7d32; }
.badge-rejected { background: #ffebee; color: #c62828; }

/* ボタン */
button {
    border-radius: 6px;
    transition: 0.2s;
    font-weight: bold;
    cursor: pointer;
    border: none;
}

#submitBtn {
    background-color: #1a73e8;
    color: white;
    padding: 12px 40px;
    font-size: 16px;
}
#submitBtn:hover { background-color: #1557b0; }
EOT
) !!}</code></pre>          
        </li>

        <li>反映と運用のヒント</br>
            <ol>
                <li><strong>アップロード: </strong>ローカルで作成したこのファイルを AWS（パブリック IPv4 アドレス）の /var/www/html/Attendance/resources/views/admin/expenses/index.blade.php に上書きしてください。</li>
                <li><strong>領収書の確認: </strong>領収書（画像）が正しく表示されない場合は、php artisan storage:link が実行されているか、または AWS 上の storage/app/public のシンボリックリンクが正しいかを確認してください。</li>
                <li><strong>デザインの統一: </strong> 金額の「右寄せ（text-align: right）」とフォント（等幅フォント）を適用することで、経理担当者が数値を読み取りやすいように配慮しています。</li>
            </ol>
            <p>これで、管理者メニューからスムーズに経費の承認作業が行えるようになります。</p>
        </li>

        <li>構築のロードマップ</br>
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
                保存パス: resources/views/guide.blade.php</br>
            HTML
            </p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5;"><code>{!! e(<<<'EOT'
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>操作ガイド | 業務管理システム</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; line-height: 1.6; }
        .header { background: #1a73e8; color: white; padding: 20px; text-align: center; }
        .container { max-width: 900px; margin: 30px auto; background: white; padding: 40px; border-radius: 12px; shadow: 0 4px 20px rgba(0,0,0,0.05); }
        
        h2 { border-left: 5px solid #1a73e8; padding-left: 15px; margin-top: 40px; color: #1a73e8; }
        .step { display: flex; margin-bottom: 30px; align-items: flex-start; }
        .step-number { background: #1a73e8; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 15px; flex-shrink: 0; }
        .step-content { flex-grow: 1; }
        .step-content strong { display: block; font-size: 1.1em; margin-bottom: 5px; }
        
        .info-box { background: #e8f0fe; border-radius: 8px; padding: 20px; margin: 20px 0; border-left: 5px solid #4285f4; }
        .img-placeholder { background: #eee; border: 1px dashed #ccc; padding: 40px; text-align: center; color: #999; margin: 15px 0; border-radius: 8px; }
        
        .footer-nav { text-align: center; margin-top: 50px; padding-bottom: 50px; }
        .btn-back { display: inline-block; background: #666; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; }
    </style>
</head>
<body>

<div class="header">
    <h1>📖 システム操作ガイド</h1>
    <p>初めて利用する方への使いかた解説</p>
</div>

<div class="container">
    <p>このシステムでは、日々の「勤怠打刻」と「経費精算」を行うことができます。以下の手順に従って操作してください。</p>

    <h2>1. 勤怠管理（出退勤の打刻）</h2>
    <div class="step">
        <div class="step-number">1</div>
        <div class="step-content">
            <strong>ダッシュボードから「勤怠管理」を選択</strong>
            メニューから勤怠画面を開きます。
        </div>
    </div>
    <div class="step">
        <div class="step-number">2</div>
        <div class="step-content">
            <strong>出勤・退勤ボタンをクリック</strong>
            出社時に「出勤」、退社時に「退勤」ボタンを押すだけで時刻が記録されます。
        </div>
    </div>
    <div class="info-box">
        <strong>💡 修正が必要な場合</strong><br>
        打刻を忘れたり修正したい場合は、管理者に修正依頼を出してください。
    </div>

    <h2>2. 経費精算の申請</h2>
    <div class="step">
        <div class="step-number">1</div>
        <div class="step-content">
            <strong>「経費精算」メニューへ移動</strong>
            新規申請ボタンを押して入力フォームを開きます。
        </div>
    </div>
    <div class="step">
        <div class="step-number">2</div>
        <div class="step-content">
            <strong>内容の入力と領収書の添付</strong>
            日付、項目（交通費など）、金額を入力し、領収書をスマホで撮影してアップロードしてください。
        </div>
    </div>

    <h2>3. 休暇・残業の申請</h2>
    <p>有給休暇や残業の申請は、勤怠画面内の「申請フォーム」から行えます。管理者が承認すると、自動的に勤務表へ反映されます。</p>
</div>

<div class="footer-nav">
    <a href="{{ route('dashboard') }}" class="btn-back">ダッシュボードへ戻る</a>
</div>

</body>
</html>
EOT
) !!}</code></pre>  
        </li>

        <li>ルーティングの設定 (routes/web.php)</br>
        <p>
            一般ユーザーがログイン後にアクセスできるよう設定します。</br>
            PHP
        </p>
        <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5;"><code>{!! e(<<<'EOT'
Route::middleware(['auth'])->group(function () {
    // 操作ガイドの表示
    Route::get('/guide', function () {
        return view('guide');
    })->name('guide.show');
});
EOT
) !!}</code></pre>  

        <li>マニュアルPDFへの導線（オプション）</br>
            <p>
                以前作成した「PDFマニュアル表示機能」と組み合わせるのも効果的です。</br>
                ガイドページの冒頭に以下のリンクを置くと、より詳細な情報を補足できます。</br>
                HTML
            </p>
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
        <li>Viewファイルの作成（表示内容）</br>
            <p>
                まず、ブラウザに表示される「中身」を作成します。</br>
                保存場所: resources/views/guide_expense.blade.php</br>
                内容: 
            </p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5;"><code>{!! e(<<<'EOT'
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
EOT
) !!}</code></pre>  
        </li>

        <li>ルーティングの追加（住所の設定）</br>
            <p>
                編集ファイル: routes/web.php</br>
                追記するコード:</br>
                PHP
            </p>
            <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5;"><code>{!! e(<<<'EOT'
// 経費精算ガイド用のルート
Route::get('/guide_expense', function () {
    return view('guide_expense'); // resources/views/guide_expense.blade.php を呼ぶ
})->name('guide_expense.show');
EOT
) !!}</code></pre> 
        </li>

        <li>各画面へのリンク設置（入り口の作成）</br>
        <p>ユーザーが迷わないよう、主要な画面にガイドへのボタンを設置します。</p>
        <ol>
            <li>
                <p>
                    サインアウトボタンの横に設置します。</br>
                    HTML
                </p>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5;"><code>{!! e(<<<'EOT'
<div class="top-left" style="display: flex; align-items: center;">
    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
        @csrf
        <button type="submit" class="big-text">サインアウト</button>
    </form>
    <a href="{{ route('guide_expense.show') }}" class="big-text" style="margin-left: 10px; text-decoration: none;">📖 ガイド</a>
</div>
EOT
) !!}</code></pre> 
            </li>

            <li>勤務表画面 (attendance.blade.php)</br>
                <p>
                    経費精算ボタンの横に設置します。</br>
                    HTML
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

            <li>チェックリスト（動かない場合）</br>
            <p>構築後、もしエラーが出る場合は以下を確認してください。</p>
            <ol>
                <li><strong>1.	ファイル名のスペル: </strong>resources/views/ の中にあるファイル名が guide_expense.blade.php と一文字違わず一致しているか。</li>
                <li><strong>2.	キャッシュのクリア: </strong>変更が反映されない場合、ターミナルで php artisan view:clear を実行してください。</li>
                <li><strong>3.	ルート名の不一致: </strong> web.php で書いた ->name('...') の中身と、リンクの route('...') の中身が同じか。</li>
            </ol>
            <p>この手順通りに進めれば、どの画面からでもスムーズにガイドを参照できるようになります。</p>
            </li>
        </ol>
        </li>
    </ol>
</section>