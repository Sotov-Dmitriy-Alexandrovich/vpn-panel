<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>VPN Panel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/logo.svg') }}">
</head>
<body>
<nav class="header">
    <div class="nav-left">
        <img class="logo" src="{{asset('img/header_logo.svg')}}" width="70" height="40" alt="Логотип">

        <!-- Кнопка бургера (видна только на мобильных) -->
        <button class="burger" id="burger" aria-label="Меню">
            <span class="burger-line"></span>
            <span class="burger-line"></span>
            <span class="burger-line"></span>
        </button>

        <div class="navigation_header" id="navMenu">
            <a href="/connected"
               class="nav-link {{ request()->route('page')=='connected'?'active':'' }}">Подключенные</a>
            <a href="/add" class="nav-link {{ request()->route('page')=='add'?'active':'' }}">Подключить</a>
            <a href="/status" class="nav-link {{ request()->route('page')=='status'?'active':'' }}">Статус</a>
            <form class="navigation-btn" method="POST" action="/logout">
                @csrf
                <button type="submit" class="btn-logout"
                        style="background:#d25afa;color:#fff;border:none;padding:.5rem 1rem;border-radius:6px;cursor:pointer">
                    Выйти
                </button>
            </form>
        </div>
    </div>
</nav>
<main class="container">@yield('content')</main>
<script>window.csrf = "{{ csrf_token() }}";</script>
<script src="/js/app.js"></script>

<script>
    (function () {
        const burger = document.getElementById('burger');
        const navMenu = document.getElementById('navMenu');
        const body = document.body;

        if (!burger || !navMenu) return;

        burger.addEventListener('click', function () {
            const isOpen = burger.classList.toggle('active');
            navMenu.classList.toggle('active');
            body.classList.toggle('no-scroll', isOpen);
        });

        // Закрывать меню при клике на ссылку (на мобильных)
        navMenu.querySelectorAll('.nav-link, .btn-logout').forEach(el => {
            el.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    burger.classList.remove('active');
                    navMenu.classList.remove('active');
                    body.classList.remove('no-scroll');
                }
            });
        });

        // Сбрасывать состояние при ресайзе
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                burger.classList.remove('active');
                navMenu.classList.remove('active');
                body.classList.remove('no-scroll');
            }
        });
    })();
</script>
</body>
</html>
