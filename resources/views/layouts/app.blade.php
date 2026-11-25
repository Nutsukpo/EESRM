
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') | EES Restaurant Manager</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
          rel="stylesheet">

    <style>
        :root {
            --ees-primary: #173b2d;
            --ees-primary-light: #245641;
            --ees-primary-dark: #102b20;
            --ees-accent: #e9a23b;
            --ees-accent-light: #f8d99b;
            --ees-sidebar-width: 255px;
            --ees-navbar-height: 70px;
            --ees-body-bg: #f5f7f6;
            --ees-text: #25332c;
            --ees-muted: #7b8981;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: var(--ees-body-bg);
            color: var(--ees-text);
            font-family: 'DM Sans', system-ui, -apple-system,
                         BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 14px;
        }

        body.preloader-active {
            overflow: hidden;
        }

        a {
            transition: color .2s ease, background .2s ease;
        }

        /* ==========================================
           BEAUTIFUL FULL-SCREEN PRELOADER
        ========================================== */

        #eesPreloader {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background:
                radial-gradient(
                    circle at 50% 42%,
                    rgba(46, 100, 70, .48) 0%,
                    rgba(23, 59, 45, .15) 35%,
                    transparent 65%
                ),
                linear-gradient(
                    145deg,
                    #10271d 0%,
                    #173b2d 48%,
                    #0d241a 100%
                );
            opacity: 1;
            visibility: visible;
            transition:
                opacity .65s ease,
                visibility .65s ease;
        }

        #eesPreloader.is-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .preloader-glow {
            position: absolute;
            width: 430px;
            height: 430px;
            border-radius: 50%;
            background: rgba(233, 162, 59, .075);
            filter: blur(75px);
            animation: preloaderGlow 3.5s ease-in-out infinite alternate;
        }

        .preloader-orbit {
            position: absolute;
            width: 350px;
            height: 350px;
            border: 1px solid rgba(233, 162, 59, .09);
            border-radius: 50%;
            animation: orbitPulse 4s ease-in-out infinite;
        }

        .preloader-orbit::before,
        .preloader-orbit::after {
            content: "";
            position: absolute;
            border: 1px solid rgba(255, 255, 255, .045);
            border-radius: 50%;
        }

        .preloader-orbit::before {
            inset: 22px;
        }

        .preloader-orbit::after {
            inset: -24px;
        }

        .preloader-content {
            position: relative;
            z-index: 2;
            width: min(90%, 340px);
            text-align: center;
            animation: preloaderEnter .8s ease both;
        }

        .preloader-emblem {
            position: relative;
            width: 122px;
            height: 122px;
            margin: 0 auto 29px;
            display: grid;
            place-items: center;
        }

        .preloader-ring {
            position: absolute;
            inset: 0;
            border: 2px solid rgba(233, 162, 59, .15);
            border-top-color: var(--ees-accent);
            border-right-color: var(--ees-accent-light);
            border-radius: 50%;
            animation: preloaderSpin 1.8s linear infinite;
        }

        .preloader-ring-inner {
            position: absolute;
            inset: 10px;
            border: 1px dashed rgba(248, 217, 155, .45);
            border-radius: 50%;
            animation: preloaderSpinReverse 12s linear infinite;
        }

        .preloader-icon {
            position: relative;
            width: 82px;
            height: 82px;
            display: grid;
            place-items: center;
            border-radius: 25px;
            background: linear-gradient(145deg, #f8d99b, #e9a23b);
            color: #173b2d;
            font-size: 36px;
            box-shadow:
                0 0 35px rgba(233, 162, 59, .18),
                inset 0 1px 0 rgba(255, 255, 255, .6);
            animation: clocheFloat 2.2s ease-in-out infinite;
        }

        .preloader-sparkle {
            position: absolute;
            color: var(--ees-accent-light);
            font-size: 13px;
            animation: sparkle 2s ease-in-out infinite;
        }

        .preloader-sparkle.one {
            top: 13px;
            right: 3px;
        }

        .preloader-sparkle.two {
            bottom: 17px;
            left: 0;
            animation-delay: .7s;
        }

        .preloader-brand {
            color: #fff;
            font-size: 26px;
            line-height: 1.25;
            letter-spacing: 1.7px;
            font-weight: 800;
            margin: 0;
        }

        .preloader-brand span {
            color: var(--ees-accent);
        }

        .preloader-tagline {
            margin-top: 10px;
            color: #bfd0c5;
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .preloader-divider {
            width: 44px;
            height: 2px;
            margin: 23px auto;
            border-radius: 5px;
            background: linear-gradient(
                90deg,
                transparent,
                var(--ees-accent),
                transparent
            );
        }

        .preloader-status {
            color: #e0e9e3;
            font-size: 12px;
            letter-spacing: .5px;
            margin-bottom: 14px;
        }

        .preloader-track {
            width: 210px;
            max-width: 100%;
            height: 3px;
            overflow: hidden;
            margin: 0 auto;
            border-radius: 20px;
            background: rgba(255, 255, 255, .13);
        }

        .preloader-progress {
            width: 35%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(
                90deg,
                #d48b25,
                #ffe3a3,
                #e9a23b
            );
            box-shadow: 0 0 12px rgba(233, 162, 59, .55);
            animation: progressTravel 1.5s ease-in-out infinite;
        }

        .preloader-footer {
            margin-top: 32px;
            color: #91ac9b;
            font-size: 10px;
            letter-spacing: 1.2px;
        }

        @keyframes preloaderSpin {
            to { transform: rotate(360deg); }
        }

        @keyframes preloaderSpinReverse {
            to { transform: rotate(-360deg); }
        }

        @keyframes clocheFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        @keyframes preloaderGlow {
            from { transform: scale(.9); opacity: .65; }
            to { transform: scale(1.12); opacity: 1; }
        }

        @keyframes orbitPulse {
            0%, 100% { transform: scale(.97); opacity: .5; }
            50% { transform: scale(1.04); opacity: 1; }
        }

        @keyframes sparkle {
            0%, 100% { opacity: .35; transform: scale(.8); }
            50% { opacity: 1; transform: scale(1.25); }
        }

        @keyframes progressTravel {
            0% { transform: translateX(-110%); }
            100% { transform: translateX(310%); }
        }

        @keyframes preloaderEnter {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ==========================================
           SIDEBAR
        ========================================== */

        .app-sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: var(--ees-sidebar-width);
            background: var(--ees-primary);
            color: #fff;
            z-index: 1040;
            overflow-y: auto;
            transition: transform .25s ease;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.2) transparent;
        }

        .sidebar-brand {
            min-height: var(--ees-navbar-height);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,.12);
        }

        .brand-mark {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border-radius: 10px;
            background: var(--ees-accent);
            color: var(--ees-primary-dark);
            display: grid;
            place-items: center;
            font-size: 20px;
        }

        .brand-name {
            font-size: 15px;
            font-weight: 800;
            margin: 0;
        }

        .brand-subtitle {
            font-size: 11px;
            color: #c6d7cd;
            margin: 2px 0 0;
        }

        .sidebar-section {
            padding: 22px 20px 8px;
            color: #a9c2b4;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 700;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 3px 12px;
            padding: 11px 13px;
            border-radius: 7px;
            color: #dce9e1;
            text-decoration: none;
            transition: background .2s ease, color .2s ease;
        }

        .sidebar-link i {
            width: 19px;
            text-align: center;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            color: #fff;
            background: var(--ees-primary-light);
        }

        .sidebar-link.active {
            box-shadow: inset 3px 0 var(--ees-accent);
        }

        .sidebar-footer {
            margin: 25px 12px 15px;
            padding: 15px;
            border-top: 1px solid rgba(255,255,255,.12);
            color: #c6d7cd;
            font-size: 12px;
        }

        /* ==========================================
           MAIN CONTENT AND NAVBAR
        ========================================== */

        .app-main {
            margin-left: var(--ees-sidebar-width);
            min-height: 100vh;
        }

        .app-navbar {
            min-height: var(--ees-navbar-height);
            position: sticky;
            top: 0;
            z-index: 1020;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #e8ece9;
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .navbar-heading {
            font-size: 18px;
            font-weight: 800;
            margin: 0;
        }

        .navbar-subheading {
            color: var(--ees-muted);
            font-size: 12px;
            margin-top: 2px;
        }

        .app-content {
            padding: 28px;
            animation: contentEnter .35s ease both;
        }

        @keyframes contentEnter {
            from { opacity: .75; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-heading {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .page-description {
            color: #758178;
            margin-bottom: 24px;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #e4eee8;
            color: var(--ees-primary);
            font-weight: 800;
            flex-shrink: 0;
        }

        .content-card {
            background: #fff;
            border: 1px solid #e8ece9;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(20, 45, 31, .03);
        }

        .btn,
        .form-control,
        .form-select {
            border-radius: 7px;
        }

        .btn:focus-visible,
        a:focus-visible,
        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible {
            outline: 3px solid rgba(233, 162, 59, .5);
            outline-offset: 2px;
        }

        .sidebar-overlay {
            display: none;
        }

        /* ==========================================
           RESPONSIVE DESIGN
        ========================================== */

        @media (max-width: 991.98px) {
            .app-sidebar {
                transform: translateX(-100%);
            }

            .app-sidebar.show {
                transform: translateX(0);
            }

            .sidebar-overlay.show {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 1030;
                background: rgba(0, 0, 0, .45);
            }

            .app-main {
                margin-left: 0;
            }

            .app-navbar {
                padding: 0 16px;
            }

            .app-content {
                padding: 20px 16px;
            }
        }

        @media (max-width: 575.98px) {
            .preloader-emblem {
                width: 108px;
                height: 108px;
            }

            .preloader-icon {
                width: 74px;
                height: 74px;
                font-size: 32px;
            }

            .preloader-brand {
                font-size: 22px;
            }

            .preloader-orbit {
                width: 285px;
                height: 285px;
            }

            .navbar-heading {
                font-size: 16px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body class="preloader-active">

    <!-- ========================================
         EES RESTAURANT MANAGER PRELOADER
    ========================================= -->
    <div id="eesPreloader"
         role="status"
         aria-live="polite"
         aria-label="Loading EES Restaurant Manager">

        <div class="preloader-glow"></div>
        <div class="preloader-orbit"></div>

        <div class="preloader-content">

            <div class="preloader-emblem">
                <div class="preloader-ring"></div>
                <div class="preloader-ring-inner"></div>

                <div class="preloader-icon">
                    <i class="fa-solid fa-bell-concierge"
                       aria-hidden="true"></i>
                </div>

                <i class="fa-solid fa-sparkles preloader-sparkle one"
                   aria-hidden="true"></i>

                <i class="fa-solid fa-star preloader-sparkle two"
                   aria-hidden="true"></i>
            </div>

            <h1 class="preloader-brand">
                EES <span>RESTAURANT</span>
            </h1>

            <p class="preloader-tagline">Manager</p>

            <div class="preloader-divider"></div>

            <p class="preloader-status" id="preloaderStatus">
                Preparing your workspace...
            </p>

            <div class="preloader-track"
                 role="progressbar"
                 aria-label="Loading page">
                <div class="preloader-progress"></div>
            </div>

            <p class="preloader-footer">
                SERVICE · QUALITY · EXCELLENCE
            </p>
        </div>
    </div>

    <!-- SIDEBAR -->
    @include('partials.sidebar')

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- APPLICATION -->
    <div class="app-main">

        @include('partials.navbar')

        <main class="app-content">

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show"
                     role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>
                    {{ session('status') }}

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Close"></button>
                </div>
            @endif

            @yield('content')

        </main>
    </div>

    <!-- BOOTSTRAP -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- APPLICATION SCRIPTS -->
    <script>
        (() => {
            'use strict';

            const preloader = document.getElementById('eesPreloader');
            const statusText = document.getElementById('preloaderStatus');
            const sidebar = document.getElementById('appSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggle = document.getElementById('sidebarToggle');

            let preloaderDismissed = false;

            function hidePreloader() {
                if (preloaderDismissed || !preloader) {
                    return;
                }

                preloaderDismissed = true;
                preloader.classList.add('is-hidden');
                document.body.classList.remove('preloader-active');

                window.setTimeout(() => {
                    preloader.remove();
                }, 750);
            }

            function closeSidebar() {
                sidebar?.classList.remove('show');
                overlay?.classList.remove('show');

                toggle?.setAttribute('aria-expanded', 'false');
            }

            toggle?.setAttribute('aria-expanded', 'false');

            toggle?.addEventListener('click', () => {
                const isOpen = sidebar?.classList.toggle('show') ?? false;

                overlay?.classList.toggle('show', isOpen);
                toggle?.setAttribute('aria-expanded', String(isOpen));
            });

            overlay?.addEventListener('click', closeSidebar);

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 992) {
                    closeSidebar();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeSidebar();
                }
            });

            // Show a short status sequence while the page loads.
            const loadingMessages = [
                'Preparing your workspace...',
                'Organizing your dashboard...',
                'Almost ready...'
            ];

            let messageIndex = 0;

            const messageTimer = window.setInterval(() => {
                if (preloaderDismissed) {
                    window.clearInterval(messageTimer);
                    return;
                }

                messageIndex = (messageIndex + 1) % loadingMessages.length;

                if (statusText) {
                    statusText.textContent = loadingMessages[messageIndex];
                }
            }, 900);

            // Hide once all page resources have loaded.
            if (document.readyState === 'complete') {
                window.setTimeout(hidePreloader, 350);
            } else {
                window.addEventListener('load', () => {
                    window.setTimeout(hidePreloader, 350);
                }, { once: true });
            }

            // Failsafe: never leave the preloader blocking the application.
            window.setTimeout(hidePreloader, 8000);
        })();
    </script>

    @stack('scripts')

</body>
</html>
