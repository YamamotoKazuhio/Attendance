{{-- resources/views/top_page.blade.php --}}

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠・経費管理システム</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f0f2f5; margin: 0; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .container { text-align: center; background: white; padding: 50px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 500px; width: 90%; }
        h1 { color: #1a73e8; margin-bottom: 10px; font-size: 24px; }
        p { color: #5f6368; margin-bottom: 30px; }
        .btn-login { display: inline-block; background-color: #1a73e8; color: white; padding: 12px 40px; text-decoration: none; border-radius: 6px; font-weight: bold; transition: background 0.3s; }
        .btn-login:hover { background-color: #1557b0; }
        .system-list { list-style: none; padding: 0; margin-bottom: 30px; text-align: left; display: inline-block; }
        .system-list li { margin: 10px 0; color: #3c4043; }
        .system-list li::before { content: "✅"; margin-right: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h1>業務管理ポータル</h1>
    <p>社内システムへようこそ</p>
    
    <ul class="system-list">
        <li>勤怠管理システム</li>
        <li>経費精算管理システム</li>
    </ul>

    <div style="margin-top: 20px;">
        <a href="{{ route('login') }}" class="btn-login">ログイン画面へ</a>
    </div>
</div>

</body>
</html>