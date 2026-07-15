<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>VPN Panel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <nav class="header">
        <div class="nav-left">
            <a href="/connected" class="nav-link {{ request()->route('page')=='connected'?'active':'' }}">Подключенные</a>
            <a href="/add" class="nav-link {{ request()->route('page')=='add'?'active':'' }}">Подключить</a>
            <a href="/status" class="nav-link {{ request()->route('page')=='status'?'active':'' }}">Статус</a>
        </div>
        <form method="POST" action="/logout">
            @csrf
            <button type="submit" class="btn-logout" style="background:#ef4444;color:#fff;border:none;padding:.5rem 1rem;border-radius:6px;cursor:pointer">Выйти</button>
        </form>
    </nav>
    <main class="container">@yield('content')</main>
    <script>window.csrf="{{ csrf_token() }}";</script>
    <script src="/js/app.js"></script>
</body>
</html>
