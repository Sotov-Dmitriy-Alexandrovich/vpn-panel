<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Вход | VPN Panel</title>
    <style>
        body{margin:0;font-family:system-ui,sans-serif;background:#0f172a;color:#e2e8f0;display:flex;align-items:center;justify-content:center;height:100vh}
        .box{background:#1e293b;padding:2rem;border-radius:12px;width:100%;max-width:360px;box-shadow:0 10px 30px rgba(0,0,0,.4)}
        h2{text-align:center;margin:0 0 1.5rem;color:#f8fafc}
        input{width:100%;padding:.9rem;background:#334155;color:#fff;border:1px solid #475569;border-radius:8px;margin-bottom:.8rem;box-sizing:border-box;font-size:1rem}
        button{width:100%;padding:.9rem;background:#3b82f6;color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:1rem}
        button:hover{background:#2563eb}
        .err{color:#ef4444;background:#3f1e1e;padding:.6rem;border-radius:6px;margin-bottom:1rem;font-size:.9rem;text-align:center}
        .hint{color:#64748b;font-size:.8rem;text-align:center;margin-top:1rem}
    </style>
</head>
<body>
    <div class="box">
        <h2>🔐 VPN Admin</h2>
        @if($errors->any())
            <div class="err">{{ $errors->first('email') }}</div>
        @endif
        <form method="POST" action="/login">
            @csrf
            <input type="email" name="email" placeholder="Email" required value="{{ old('email') }}">
            <input type="password" name="password" placeholder="Пароль" required>
            <label style="display:flex;align-items:center;gap:6px;margin-bottom:1rem;font-size:.9rem;color:#94a3b8;cursor:pointer">
                <input type="checkbox" name="remember" style="width:auto;margin:0;accent-color:#3b82f6"> Запомнить меня
            </label>
            <button type="submit">Войти</button>
        </form>
    </div>
</body>
</html>
