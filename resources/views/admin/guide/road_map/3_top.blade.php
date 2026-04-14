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
        <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; max-height: 300px; overflow-y: auto;"><code>&lt;?php
        
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
        ファイル名: resources/views/top_page.blade.php</br>
        以下の内容を新しいファイルとして保存します。
    </p>
        <span onclick="openCodeModal('codeBlock_top')" style="color: #007bff; cursor: pointer; text-decoration: underline; font-weight: bold;">
            <p style="margin-bottom: 5px; font-size: 1rem;">HTML</p>
        </span>

        <div id="codeBlock_top" style="display: none;">
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; text-align: left;"><code>@php
            $path = base_path('resources/views/top_page.blade.php');
            if (file_exists($path)) {
                echo e(file_get_contents($path));
            } else {
                echo "ファイルが見つかりません: " . $path;
            }
        @endphp</code></pre>
        </div>
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



