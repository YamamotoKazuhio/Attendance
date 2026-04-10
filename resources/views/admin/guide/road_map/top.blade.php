    <section>
    <h2 id="top">3.トップページの作成</h2>
    <p>
        http://attendance.test/ にアクセスした際に最初に表示される、</br>
        <strong>「勤怠管理」と「経費精算」への入り口となるトップページ（top_page.blade.php）</strong>の作成手順を整理します。</br>
        標準の welcome 画面を置き換え、ログインしていないユーザーでも「これからどのシステムを使うか」がひと目で分かるデザインにします。</br>
        既存の attendance.blade.php や expenses.blade.php と連携する、ハブとしてのトップページ構築手順をまとめました。
    </p>
    <h3 id="controller">3-1.司令塔 (Controller)</h3>
    <p>
        ログインユーザーの「今日の打刻状況」や「申請中の経費件数」などを取得してビューに渡します。</br>
        ファイル名: app/Http/Controllers/DashboardController.php<br>
        PHP<br>
        <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;?php
        
namespace App\Http\Controllers;
        
use App\Models\Attendance;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;<
        
class DashboardController extends Controller<
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');

        // 今日の打刻データを確認
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        // 自分が申請中の経費件数（status=1: 申請中）
        $myPendingExpenses = Expense::where('user_id', $user->id)
            ->where('status', 1)
            ->count();

        return view('dashboard', compact('user', 'todayAttendance', 'myPendingExpenses'));
    }
}
</code></pre>
    </p>

    <h3 id="routes">3-2. URLの設定 (Routes)</h3>
    <p>
        ログイン後のデフォルト画面として設定します。</br>
    ファイル名: routes/web.php</br>
    PHP</br>
        <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;use App\Http\Controllers\DashboardController;

Route::middleware(['auth'])->group(function () {
    // トップページ（ダッシュボード）
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // ルートURL「/」にアクセスした際もダッシュボードへ飛ばす設定
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
});
</code></pre>
    </p>

    <h3 id="view">3-3.トップページ画面 (View)</h3>
    <p>
        各機能へのショートカットをカード形式で配置し、現在の状態を一目でわかるようにします。</br>
        ファイル名: resources/views/dashboard.blade.php</br>
        以下の内容を新しいファイルとして保存します。</br>
        HTML</br>
        <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>&lt;!DOCTYPE html&gt;
&lt;html lang="ja"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;勤怠・経費管理システム&lt;/title&gt;
    &lt;style&gt;
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f0f2f5; margin: 0; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .container { text-align: center; background: white; padding: 50px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 500px; width: 90%; }
        h1 { color: #1a73e8; margin-bottom: 10px; font-size: 24px; }
        p { color: #5f6368; margin-bottom: 30px; }
        .btn-login { display: inline-block; background-color: #1a73e8; color: white; padding: 12px 40px; text-decoration: none; border-radius: 6px; font-weight: bold; transition: background 0.3s; }
        .btn-login:hover { background-color: #1557b0; }
        .system-list { list-style: none; padding: 0; margin-bottom: 30px; text-align: left; display: inline-block; }
        .system-list li { margin: 10px 0; color: #3c4043; }
        .system-list li::before { content: "✅"; margin-right: 10px; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;

&lt;div class="container"&gt;
    &lt;h1&gt;業務管理ポータル&lt;/h1&gt;
    &lt;p&gt;社内システムへようこそ&lt;/p&gt;
    
    &lt;ul class="system-list"&gt;
        &lt;li&gt;勤怠管理システム&lt;/li&gt;
        &lt;li&gt;経費精算管理システム&lt;/li&gt;
    &lt;/ul&gt;

    &lt;div style="margin-top: 20px;"&gt;
        &lt;a href="{{ route('login') }}" class="btn-login"&gt;ログイン画面へ&lt;/a&gt;
    &lt;/div&gt;
&lt;/div&gt;

&lt;/body&gt;
&lt;/html&gt;
</code></pre>
</code></pre>
    </p>

    <h3 id="construction-roadmap">3-4.構築のロードマップ</h3>
    <ol>
        <li><strong>Controllerの作成</strong>: DashboardController を作成し、必要なデータをまとめます。</li>
        <li><strong>Routeの設定</strong>: ログイン後のリダイレクト先（AuthenticatedSessionController.php など）を</br>
             /dashboard に向けるよう調整します。</li>
        <li><strong>Viewの作成</strong>: 既存の各機能（勤務表、経費、ガイド）へスムーズに移動できるボタンを配置します。</li>
        <li><strong>管理者導線の確認</strong>: is_admin フラグを見て、管理者メニューへのリンクが表示されるかテストします。</li>
    </ol>
    <p>このトップページを「起点」にすることで、ユーザーは「次に何をすべきか（打刻忘れはないか、申請は通ったか）」をログイン直後に把握できるようになります。</p>

</section>



