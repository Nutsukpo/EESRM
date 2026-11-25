<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f2c21">
    <meta name="robots" content="noindex, nofollow">

    <title>Sign In | EES Restaurant Manager</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --green: #173b2d;
            --green-2: #245641;
            --green-3: #e6f0ea;
            --gold: #e9a23b;
            --gold-soft: #f3c36e;
            --bg: #f5f7f6;
            --ink: #26372e;
            --muted: #6b7a71;
            --line: #dfe6e1;
            --danger: #c0392b;
        }

        html, body { height: 100%; }

        body {
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--ink);
            background: #0f2c21;
            -webkit-font-smoothing: antialiased;
        }

        /* =========================================================
           Animated stage (background)
        ========================================================= */
        .stage { position: fixed; inset: 0; z-index: 0; overflow: hidden; background: #0f2c21; contain: layout paint style; }
        .scene, .stage-video { position: absolute; inset: 0; width: 100%; height: 100%; }
        .stage-video { object-fit: cover; }

        .stage-overlay {
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(ellipse at 50% 120%, rgba(0,0,0,.0) 40%, rgba(5,18,13,.55) 100%),
                linear-gradient(90deg, rgba(8,26,19,.28) 0%, rgba(8,26,19,.38) 55%, rgba(8,26,19,.72) 100%);
        }

        /* ---- people ---- */
        .skin  { fill: var(--skin); }
        .hair  { fill: var(--hair); }
        .shirt { fill: var(--shirt); }
        .shirt2 { fill: var(--shirt); stroke: rgba(0,0,0,.2); stroke-width: 1; }
        .pants { fill: var(--pants); }
        .shoe  { fill: #1b1613; }
        .ear   { fill: var(--skin); stroke: rgba(0,0,0,.2); stroke-width: 1; }
        .cheek { fill: #ff6b6b; opacity: .25; }
        .eye, .mouth { fill: #2a1a12; }
        .bite  { fill: #d8742a; }

        .upper { animation: upper 5.2s ease-in-out infinite; animation-delay: var(--d, 0s); }
        .fore  { animation: fore 5.2s ease-in-out infinite;  animation-delay: var(--d, 0s); }
        .head  { animation: lean 5.2s ease-in-out infinite;  animation-delay: var(--d, 0s); }
        .bite  { animation: bite 5.2s linear infinite;       animation-delay: var(--d, 0s); }
        .mouth { animation: chew .75s ease-in-out infinite;  animation-delay: var(--d, 0s); transform-box: fill-box; transform-origin: center; }
        .eye   { animation: blink 4.6s ease-in-out infinite; animation-delay: var(--d, 0s); transform-box: fill-box; transform-origin: center; }

        @keyframes upper { 0%, 28% { transform: rotate(-30deg); } 48%, 62% { transform: rotate(-45deg); } 84%, 100% { transform: rotate(-30deg); } }
        @keyframes fore  { 0%, 28% { transform: rotate(-50deg); } 48%, 62% { transform: rotate(-140deg); } 84%, 100% { transform: rotate(-50deg); } }
        @keyframes lean  { 0%, 28% { transform: rotate(0); } 48%, 62% { transform: rotate(6deg) translate(1px, 1px); } 84%, 100% { transform: rotate(0); } }
        @keyframes bite  { 0% { opacity: 0; } 6%, 56% { opacity: 1; } 60%, 100% { opacity: 0; } }
        @keyframes chew  { 0%, 100% { transform: scaleY(.55); } 50% { transform: scaleY(1.6); } }
        @keyframes blink { 0%, 94%, 100% { transform: scaleY(1); } 97% { transform: scaleY(.1); } }

        /* ---- food, candles, lights ---- */
        .steam { animation: steam 3.4s ease-in infinite; animation-delay: var(--d, 0s); opacity: 0; }
        @keyframes steam { 0% { transform: translateY(0); opacity: 0; } 30% { opacity: .55; } 100% { transform: translateY(-22px); opacity: 0; } }

        .flame { animation: flicker .9s ease-in-out infinite alternate; transform-box: fill-box; transform-origin: 50% 100%; }
        @keyframes flicker { 0% { transform: scale(1, 1); } 35% { transform: scale(.88, 1.14); } 70% { transform: scale(1.08, .94); } 100% { transform: scale(.95, 1.1); } }

        .glow { animation: pulse 3s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        @keyframes pulse { 0%, 100% { opacity: .75; transform: scale(1); } 50% { opacity: 1; transform: scale(1.06); } }

        .bulb { animation: twinkle 2.6s ease-in-out infinite; animation-delay: var(--d, 0s); }
        @keyframes twinkle { 0%, 100% { opacity: .35; } 50% { opacity: 1; } }

        .city { animation: twinkle 3.4s ease-in-out infinite; animation-delay: var(--d, 0s); }

        .dust { animation: rise 11s linear infinite; animation-delay: var(--d, 0s); opacity: 0; }
        @keyframes rise { 0% { transform: translateY(0); opacity: 0; } 15% { opacity: .5; } 85% { opacity: .35; } 100% { transform: translateY(-170px); opacity: 0; } }

        /* ---- waiter ---- */
        .waiter { transform: translateX(-220px); animation: stroll 42s linear infinite; }
        @keyframes stroll { from { transform: translateX(-220px); } to { transform: translateX(1820px); } }
        .leg { animation: swing .9s ease-in-out infinite alternate; }
        .leg.b { animation-delay: -.9s; }
        @keyframes swing { from { transform: rotate(-22deg); } to { transform: rotate(22deg); } }
        .waiter-body { animation: bob .9s ease-in-out infinite alternate; }
        @keyframes bob { from { transform: translateY(0); } to { transform: translateY(-2px); } }

        /* Back row is held still (cheap) and everything pauses while a sign-in request is in flight */
        .scene * { animation-play-state: var(--play, running); }
        html.busy .scene * { animation-play-state: paused !important; }

        /* =========================================================
           Layout
        ========================================================= */
        .layout {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(400px, 480px);
            gap: clamp(24px, 4vw, 72px);
            padding: clamp(24px, 4vw, 56px);
        }

        /* ---- intro (left) ---- */
        .intro {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding-bottom: 34vh;
            color: #fff;
        }

        .brand { display: inline-flex; align-items: center; gap: 14px; width: fit-content; color: inherit; text-decoration: none; }
        .brand:hover { color: inherit; }

        .brand-mark {
            width: 50px; height: 50px; flex-shrink: 0; display: grid; place-items: center;
            border-radius: 14px; font-size: 22px; color: var(--green); background: var(--gold);
            box-shadow: 0 8px 22px rgba(0,0,0,.25);
        }

        .brand-name { display: block; font-size: 19px; font-weight: 800; letter-spacing: -.02em; line-height: 1.2; }
        .brand-caption { display: block; margin-top: 3px; font-size: 12px; letter-spacing: .03em; color: rgba(255,255,255,.75); }

        .eyebrow { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 18px; font-size: 11px; font-weight: 700; letter-spacing: .22em; text-transform: uppercase; color: var(--gold-soft); }
        .eyebrow::before { content: ""; width: 26px; height: 2px; background: var(--gold); }

        .hero-title { margin: 0 0 16px; font-size: clamp(34px, 4vw, 58px); font-weight: 800; line-height: 1.08; letter-spacing: -.035em; text-shadow: 0 4px 24px rgba(0,0,0,.35); }
        .hero-title span { color: var(--gold); }
        .hero-text { max-width: 480px; margin: 0; font-size: 16px; line-height: 1.7; color: rgba(255,255,255,.86); text-shadow: 0 2px 12px rgba(0,0,0,.35); }

        .chips { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; }
        .chip {
            display: inline-flex; align-items: center; gap: 9px; padding: 9px 14px; border-radius: 999px;
            font-size: 13px; font-weight: 600; color: #fff;
            background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.18);
        }
        .chip i { color: var(--gold); }

        /* ---- login card (right) ---- */
        .login { display: flex; align-items: center; justify-content: center; }

        .card-login {
            width: 100%;
            max-width: 450px;
            padding: clamp(26px, 3.2vw, 38px);
            border-radius: 24px;
            background: rgba(255,255,255,.97);
            border: 1px solid rgba(255,255,255,.6);
            box-shadow: 0 30px 80px rgba(0,0,0,.40);
        }

        .mobile-brand { display: none; margin-bottom: 26px; color: var(--green); }
        .mobile-brand .brand-caption { color: var(--muted); }

        .heading { margin: 0 0 8px; font-size: 28px; font-weight: 800; letter-spacing: -.03em; color: var(--green); }
        .subheading { margin: 0 0 26px; font-size: 14px; line-height: 1.65; color: var(--muted); }

        .notice { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 20px; padding: 12px 14px; border: 1px solid; border-radius: 12px; font-size: 13px; line-height: 1.5; }
        .notice i { margin-top: 2px; }
        .notice.success { color: #14633f; background: #e9f7ef; border-color: #c4e8d3; }
        .notice.error { color: #9c2f24; background: #fdeeec; border-color: #f4cdc8; }

        .field { margin-bottom: 18px; }
        .label { display: block; margin-bottom: 8px; font-size: 12.5px; font-weight: 600; color: #34473c; }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; top: 50%; left: 16px; transform: translateY(-50%); color: #8a968f; pointer-events: none; z-index: 2; }

        .form-control {
            height: 52px; padding: 0 16px 0 44px; border: 1px solid var(--line); border-radius: 12px;
            font-size: 14px; color: var(--ink); background-color: #fff;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .form-control::placeholder { color: #a4ada7; }
        .form-control:focus { border-color: var(--green-2); box-shadow: 0 0 0 4px rgba(36,86,65,.12); }
        .form-control.is-invalid { border-color: var(--danger); background-image: none; }
        .form-control.is-invalid:focus { box-shadow: 0 0 0 4px rgba(192,57,43,.12); }
        .form-control.has-toggle { padding-right: 52px; }

        .field-error { margin-top: 7px; font-size: 12.5px; color: var(--danger); }

        .toggle {
            position: absolute; top: 50%; right: 8px; transform: translateY(-50%); width: 36px; height: 36px;
            border: 0; border-radius: 9px; color: #6b7a71; background: transparent; transition: background .15s ease, color .15s ease;
        }
        .toggle:hover { color: var(--green); background: var(--bg); }

        .caps-warning { display: none; margin-top: 7px; font-size: 12.5px; color: #946200; }
        .caps-warning.show { display: block; }

        .options { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 20px; }
        .form-check { margin: 0; min-height: 0; display: flex; align-items: center; gap: 8px; padding: 0; }
        .form-check-input { width: 17px; height: 17px; margin: 0; float: none; border-color: #bdc9c0; }
        .form-check-input:checked { background-color: var(--green-2); border-color: var(--green-2); }
        .form-check-label { font-size: 13px; color: #55645b; }

        .help-link { padding: 0; border: 0; background: none; font-size: 13px; font-weight: 600; color: var(--green-2); text-decoration: none; }
        .help-link:hover { color: #b87614; text-decoration: underline; }
        .help-hint { margin: -6px 0 18px; padding: 12px 14px; border-radius: 12px; font-size: 12.5px; line-height: 1.6; color: #55645b; background: var(--bg); }

        .btn-submit {
            width: 100%; height: 52px; display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            border: 0; border-radius: 12px; font-size: 14.5px; font-weight: 700; color: #fff;
            background: linear-gradient(135deg, var(--green-2), var(--green));
            box-shadow: 0 8px 20px rgba(23,59,45,.25);
            transition: box-shadow .15s ease, transform .15s ease, opacity .15s ease;
        }
        .btn-submit:hover:not(:disabled) { color: #fff; box-shadow: 0 10px 26px rgba(23,59,45,.35); transform: translateY(-1px); }
        .btn-submit:disabled { opacity: .75; cursor: wait; }

        .btn-submit:focus-visible, .toggle:focus-visible, .help-link:focus-visible, .brand:focus-visible {
            outline: 3px solid rgba(233,162,59,.75); outline-offset: 3px; border-radius: 10px;
        }

        .secure { display: flex; align-items: flex-start; gap: 12px; margin-top: 22px; padding: 14px; border: 1px solid #e1ebe4; border-radius: 14px; background: #f4f8f5; }
        .secure-icon { width: 36px; height: 36px; flex-shrink: 0; display: grid; place-items: center; border-radius: 10px; color: var(--green-2); background: var(--green-3); }
        .secure-title { margin-bottom: 3px; font-size: 12.5px; font-weight: 700; color: var(--green); }
        .secure-text { margin: 0; font-size: 12px; line-height: 1.6; color: var(--muted); }

        .legal { margin-top: 24px; text-align: center; font-size: 12px; line-height: 1.8; color: #8b968f; }
        .legal strong { color: var(--green); }

        /* =========================================================
           Responsive
        ========================================================= */
        @media (max-height: 760px) { .hero-text, .chips { display: none; } .intro { padding-bottom: 28vh; } }

        @media (max-width: 991.98px) {
            .layout { grid-template-columns: minmax(0, 1fr); padding: 24px 18px; }
            .intro { display: none; }
            .mobile-brand { display: inline-flex; }
            .card-login { background: rgba(255,255,255,.97); }
            .stage-overlay { background: linear-gradient(180deg, rgba(8,26,19,.45), rgba(8,26,19,.7)); }
        }

        @media (max-width: 420px) {
            .layout { padding: 16px 12px; }
            .heading { font-size: 25px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .scene *, .scene { animation: none !important; }
            *, *::before, *::after { transition-duration: .01ms !important; }
            .btn-submit:hover:not(:disabled) { transform: none; }
        }
    </style>
</head>

<body>

{{-- ======================================================================
     Animated background
     If you place a looping video at public/videos/diners.mp4 it replaces the
     illustrated scene automatically.
====================================================================== --}}
<div class="stage" aria-hidden="true">

    @if (file_exists(public_path('videos/diners.mp4')))

        <video class="stage-video" id="stageVideo" autoplay muted loop playsinline preload="metadata">
            <source src="{{ asset('videos/diners.mp4') }}" type="video/mp4">
        </video>

    @else

        <svg class="scene" viewBox="0 0 1600 900" preserveAspectRatio="xMidYMax slice" focusable="false">
            <defs>
                <linearGradient id="wall" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#0c241a"/>
                    <stop offset="1" stop-color="#1b4734"/>
                </linearGradient>
                <linearGradient id="floor" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#4a3523"/>
                    <stop offset="1" stop-color="#21170f"/>
                </linearGradient>
                <linearGradient id="night" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#08202c"/>
                    <stop offset="1" stop-color="#18404f"/>
                </linearGradient>
                <radialGradient id="glow">
                    <stop offset="0" stop-color="#ffcf80" stop-opacity=".55"/>
                    <stop offset="1" stop-color="#ffcf80" stop-opacity="0"/>
                </radialGradient>
                <linearGradient id="cone" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#ffd48a" stop-opacity=".32"/>
                    <stop offset="1" stop-color="#ffd48a" stop-opacity="0"/>
                </linearGradient>

                {{-- ============ A seated diner (faces right, origin = hip) ============ --}}
                <g id="diner">
                    <rect class="pants" x="-8" y="-9" width="40" height="17" rx="8.5"/>
                    <rect class="pants" x="22" y="-2" width="12" height="57" rx="6"/>
                    <rect class="shoe" x="24" y="49" width="22" height="9" rx="4.5"/>
                    <rect class="shirt" x="-14" y="-64" width="28" height="66" rx="13"/>

                    <g transform="translate(2,-62)">
                        <g class="head">
                            <rect class="skin" x="-4.5" y="-9" width="9" height="12" rx="3"/>
                            <circle class="hair" cx="-4" cy="-18" r="15.5"/>
                            <circle class="skin" cx="1" cy="-14" r="13.5"/>
                            <circle class="ear" cx="-4" cy="-13" r="3"/>
                            <circle class="cheek" cx="8" cy="-8.5" r="3"/>
                            <circle class="eye" cx="8" cy="-16" r="1.9"/>
                            <ellipse class="mouth" cx="11.5" cy="-6" rx="3" ry="1.3"/>
                        </g>
                    </g>

                    <g transform="translate(2,-50)">
                        <g class="upper">
                            <rect class="shirt2" x="-5" y="-4" width="10" height="26" rx="5"/>
                            <g transform="translate(0,22)">
                                <g class="fore">
                                    <rect class="skin" x="-3.6" y="-3" width="7.2" height="23" rx="3.6"/>
                                    <circle class="skin" cx="0" cy="20" r="4.6"/>
                                    <g transform="translate(0,20)">
                                        <rect x="-.9" y="0" width="1.8" height="8" rx=".9" fill="#cfd6da"/>
                                        <rect x="-3" y="7" width="1.3" height="7" rx=".6" fill="#cfd6da"/>
                                        <rect x="-.65" y="7" width="1.3" height="7" rx=".6" fill="#cfd6da"/>
                                        <rect x="1.7" y="7" width="1.3" height="7" rx=".6" fill="#cfd6da"/>
                                        <circle class="bite" cx="0" cy="12" r="3.8"/>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </g>
                </g>

                {{-- ============ Tableware ============ --}}
                <g id="plate">
                    <ellipse cx="0" cy="1.5" rx="38" ry="8" fill="#000" opacity=".22"/>
                    <ellipse cx="0" cy="-1" rx="36" ry="7" fill="#f4f1ea"/>
                    <ellipse cx="0" cy="-1.5" rx="26" ry="4.5" fill="#e6e1d6"/>
                </g>

                <g id="jollof">
                    <path d="M-22,-2 Q-20,-21 0,-23 Q20,-21 22,-2 Z" fill="#d8742a"/>
                    <path d="M-12,-14 q6,-6 12,0" fill="none" stroke="#f0a35a" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="-9" cy="-9" r="1.2" fill="#f0a35a"/><circle cx="6" cy="-12" r="1.2" fill="#f0a35a"/><circle cx="-2" cy="-6" r="1.2" fill="#f0a35a"/>
                    <ellipse cx="19" cy="-6" rx="10" ry="6" fill="#8c4a1f"/>
                    <circle cx="28" cy="-7" r="2.4" fill="#f3e6cf"/>
                    <circle cx="-9" cy="-24" r="1.8" fill="#3b8f4a"/><circle cx="-5" cy="-25" r="1.6" fill="#3b8f4a"/>
                </g>

                <g id="salad">
                    <path d="M-24,-3 Q-24,-24 0,-24 Q24,-24 24,-3 Z" fill="#4f9a47"/>
                    <circle cx="-9" cy="-16" r="4.5" fill="#e04a3a"/><circle cx="8" cy="-18" r="4" fill="#e04a3a"/>
                    <circle cx="0" cy="-10" r="3.6" fill="#f2c94c"/><circle cx="14" cy="-9" r="3" fill="#7bc26b"/>
                    <ellipse cx="-14" cy="-7" rx="5" ry="3" fill="#a9d36b"/>
                </g>

                <g id="drink">
                    <rect x="-7" y="-30" width="14" height="30" rx="3" fill="#fff" opacity=".25"/>
                    <rect x="-6" y="-20" width="12" height="20" rx="2" fill="#e0572f" opacity=".85"/>
                    <rect x="2" y="-40" width="2" height="24" fill="#ff7a59" transform="rotate(8)"/>
                </g>

                <g id="candle">
                    <circle class="glow" cx="0" cy="-24" r="34" fill="url(#glow)"/>
                    <rect x="-4" y="-18" width="8" height="18" rx="2" fill="#f6ead2"/>
                    <ellipse class="flame" cx="0" cy="-23" rx="3" ry="5.5" fill="#ffb347"/>
                </g>

                <path id="steam" class="steam" d="M0,0 q-6,-9 0,-18 q6,-9 0,-18" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round"/>

                {{-- ============ A table for two ============ --}}
                <g id="set">
                    <ellipse cx="0" cy="80" rx="150" ry="10" fill="#000" opacity=".3"/>
                    <rect x="-7" y="12" width="14" height="66" fill="#5a3a22"/>
                    <ellipse cx="0" cy="78" rx="40" ry="6" fill="#4a2f1b"/>
                    <rect x="-120" y="0" width="240" height="12" rx="6" fill="#9a6a3e"/>
                    <rect x="-116" y="1.5" width="232" height="3" rx="1.5" fill="#c08c5c" opacity=".6"/>

                    <use href="#plate" x="-62" y="0"/><use href="#jollof" x="-62" y="0"/>
                    <use href="#plate" x="62" y="0"/><use href="#salad" x="62" y="0"/>
                    <use href="#drink" x="-14" y="0"/><use href="#drink" x="16" y="0"/>
                    <use href="#candle" x="0" y="0"/>

                    <use href="#steam" x="-68" y="-26" style="--d:-.4s"/>
                    <use href="#steam" x="-54" y="-24" style="--d:-1.7s"/>
                    <use href="#steam" x="58" y="-26" style="--d:-1s"/>

                    <use href="#diner" transform="translate(-130,22)"
                         style="--skin:var(--a-skin);--shirt:var(--a-shirt);--hair:var(--a-hair);--pants:var(--a-pants);--d:var(--a-d)"/>
                    <use href="#diner" transform="translate(130,22) scale(-1,1)"
                         style="--skin:var(--b-skin);--shirt:var(--b-shirt);--hair:var(--b-hair);--pants:var(--b-pants);--d:var(--b-d)"/>
                </g>
            </defs>

            {{-- ===== Room ===== --}}
            <rect width="1600" height="600" fill="url(#wall)"/>
            <rect y="440" width="1600" height="160" fill="#0f2f23"/>
            <rect y="436" width="1600" height="6" fill="#6b4a2f"/>

            {{-- Window with night-city bokeh --}}
            <path d="M110,380 L110,190 Q110,80 270,80 Q430,80 430,190 L430,380 Z" fill="url(#night)" stroke="#6b4a2f" stroke-width="9"/>
            <g>
                <circle class="city" cx="170" cy="300" r="9" fill="#ffcf80" style="--d:-.4s"/>
                <circle class="city" cx="230" cy="330" r="6" fill="#fff4d6" style="--d:-1.4s"/>
                <circle class="city" cx="290" cy="290" r="11" fill="#7fd1c7" style="--d:-2.2s"/>
                <circle class="city" cx="350" cy="335" r="7" fill="#ffcf80" style="--d:-.9s"/>
                <circle class="city" cx="395" cy="285" r="8" fill="#ff9f7a" style="--d:-1.8s"/>
                <circle class="city" cx="210" cy="240" r="5" fill="#fff4d6" style="--d:-2.7s"/>
                <circle class="city" cx="320" cy="230" r="6" fill="#ffcf80" style="--d:-.2s"/>
                <circle class="city" cx="260" cy="175" r="4" fill="#7fd1c7" style="--d:-1.1s"/>
            </g>
            <path d="M270,80 V380 M110,240 H430" stroke="#6b4a2f" stroke-width="6" fill="none"/>

            {{-- String lights --}}
            <path d="M0,30 Q200,100 400,30 Q600,100 800,30 Q1000,100 1200,30 Q1400,100 1600,30" fill="none" stroke="#1c1c1c" stroke-width="2"/>
            <g fill="#ffd27a">
                <circle class="bulb" cx="100" cy="56" r="5" style="--d:-.2s"/><circle class="bulb" cx="200" cy="65" r="5" style="--d:-1.2s"/><circle class="bulb" cx="300" cy="56" r="5" style="--d:-2s"/>
                <circle class="bulb" cx="500" cy="56" r="5" style="--d:-.7s"/><circle class="bulb" cx="600" cy="65" r="5" style="--d:-1.6s"/><circle class="bulb" cx="700" cy="56" r="5" style="--d:-2.3s"/>
                <circle class="bulb" cx="900" cy="56" r="5" style="--d:-.5s"/><circle class="bulb" cx="1000" cy="65" r="5" style="--d:-1.4s"/><circle class="bulb" cx="1100" cy="56" r="5" style="--d:-2.1s"/>
                <circle class="bulb" cx="1300" cy="56" r="5" style="--d:-.9s"/><circle class="bulb" cx="1400" cy="65" r="5" style="--d:-1.9s"/><circle class="bulb" cx="1500" cy="56" r="5" style="--d:-.1s"/>
            </g>

            {{-- Floor --}}
            <rect y="600" width="1600" height="300" fill="url(#floor)"/>
            <g stroke="#000" stroke-opacity=".28" stroke-width="2">
                <line x1="0" y1="640" x2="1600" y2="640"/><line x1="0" y1="690" x2="1600" y2="690"/>
                <line x1="0" y1="760" x2="1600" y2="760"/><line x1="0" y1="850" x2="1600" y2="850"/>
            </g>

            {{-- Back row of diners (dimmed for depth) --}}
            <g style="--play:paused">
                <use href="#set" transform="translate(90,548) scale(.58)"
                     style="--a-skin:#8d5524;--a-shirt:#4f86c6;--a-hair:#1b1b1b;--a-pants:#2f3a4a;--a-d:-.6s;--b-skin:#e0ac69;--b-shirt:#c0504d;--b-hair:#3b2417;--b-pants:#34404f;--b-d:-2.4s"/>
                <use href="#set" transform="translate(430,548) scale(.58)"
                     style="--a-skin:#c68642;--a-shirt:#e9a23b;--a-hair:#111;--a-pants:#2b2f36;--a-d:-3.1s;--b-skin:#6b4423;--b-shirt:#7a5aa6;--b-hair:#111;--b-pants:#2b2f36;--b-d:-1.3s"/>
                <use href="#set" transform="translate(810,548) scale(.58)"
                     style="--a-skin:#f1c27d;--a-shirt:#3f8f7a;--a-hair:#7a4b24;--a-pants:#2e3a42;--a-d:-.9s;--b-skin:#8d5524;--b-shirt:#d8d8d8;--b-hair:#151515;--b-pants:#2b2f36;--b-d:-3.6s"/>
                <use href="#set" transform="translate(1190,548) scale(.58)"
                     style="--a-skin:#5c3a21;--a-shirt:#d96c3a;--a-hair:#0f0f0f;--a-pants:#2e3340;--a-d:-2.7s;--b-skin:#e0ac69;--b-shirt:#4f86c6;--b-hair:#5a3a1c;--b-pants:#2e3340;--b-d:-1.8s"/>
                <use href="#set" transform="translate(1500,548) scale(.58)"
                     style="--a-skin:#c68642;--a-shirt:#6a994e;--a-hair:#151515;--a-pants:#2e3340;--a-d:-1.5s;--b-skin:#f1c27d;--b-shirt:#e9a23b;--b-hair:#2b1b12;--b-pants:#2e3340;--b-d:-3.9s"/>
            </g>

            {{-- Dim the back row with a flat overlay (far cheaper than an animated filter) --}}
            <rect y="440" width="1600" height="160" fill="#06150f" opacity=".4"/>

            {{-- Waiter strolling between the rows --}}
            <g transform="translate(0,648)">
                <g class="waiter">
                    <ellipse cx="0" cy="2" rx="30" ry="5" fill="#000" opacity=".3"/>
                    <g transform="translate(-4,-52)"><g class="leg"><rect x="-4.5" y="0" width="9" height="50" rx="4" fill="#1d1f24"/><rect x="-4.5" y="46" width="16" height="7" rx="3.5" fill="#111"/></g></g>
                    <g transform="translate(4,-52)"><g class="leg b"><rect x="-4.5" y="0" width="9" height="50" rx="4" fill="#1d1f24"/><rect x="-4.5" y="46" width="16" height="7" rx="3.5" fill="#111"/></g></g>
                    <g class="waiter-body">
                        <rect x="-14" y="-112" width="28" height="62" rx="12" fill="#f3f0e8"/>
                        <rect x="-14" y="-96" width="28" height="46" rx="10" fill="#1d1f24"/>
                        <circle cx="0" cy="-126" r="13" fill="#8d5524"/>
                        <path d="M-13,-128 a13,13 0 0 1 26,-2 q-10,-6 -26,2z" fill="#111"/>
                        <line x1="8" y1="-100" x2="26" y2="-128" stroke="#f3f0e8" stroke-width="9" stroke-linecap="round"/>
                        <circle cx="27" cy="-130" r="4.5" fill="#8d5524"/>
                        <rect x="2" y="-134" width="54" height="4" rx="2" fill="#cfd6da"/>
                        <path d="M12,-134 a16,16 0 0 1 32,0z" fill="#e5eaed"/>
                        <circle cx="28" cy="-152" r="2.4" fill="#e5eaed"/>
                    </g>
                </g>
            </g>

            {{-- Foreground tables --}}
            <use href="#set" transform="translate(240,650)"
                 style="--a-skin:#8d5524;--a-shirt:#e9a23b;--a-hair:#1b1b1b;--a-pants:#2c3e50;--a-d:-1s;--b-skin:#e0ac69;--b-shirt:#3d7ea6;--b-hair:#3b2417;--b-pants:#2f3a4a;--b-d:-3.2s"/>
            <use href="#set" transform="translate(620,650)"
                 style="--a-skin:#c68642;--a-shirt:#c0392b;--a-hair:#111;--a-pants:#2b2f36;--a-d:-2.4s;--b-skin:#5c3a21;--b-shirt:#e8e8e8;--b-hair:#0f0f0f;--b-pants:#2b2f36;--b-d:-.3s"/>
            <use href="#set" transform="translate(1000,650)"
                 style="--a-skin:#f1c27d;--a-shirt:#6a994e;--a-hair:#8b5a2b;--a-pants:#2e3a42;--a-d:-3.8s;--b-skin:#8d5524;--b-shirt:#8e44ad;--b-hair:#111;--b-pants:#2b2f36;--b-d:-1.9s"/>

            {{-- Pendant lamps and light --}}
            <g>
                <g transform="translate(240,0)"><polygon points="-46,150 46,150 190,640 -190,640" fill="url(#cone)"/><circle cx="0" cy="150" r="150" fill="url(#glow)"/><line x1="0" y1="0" x2="0" y2="112" stroke="#222" stroke-width="2"/><path d="M-30,112 L30,112 L48,146 L-48,146Z" fill="#e9a23b"/><ellipse cx="0" cy="148" rx="14" ry="6" fill="#fff2c4"/></g>
                <g transform="translate(620,0)"><polygon points="-46,150 46,150 190,640 -190,640" fill="url(#cone)"/><circle cx="0" cy="150" r="150" fill="url(#glow)"/><line x1="0" y1="0" x2="0" y2="112" stroke="#222" stroke-width="2"/><path d="M-30,112 L30,112 L48,146 L-48,146Z" fill="#e9a23b"/><ellipse cx="0" cy="148" rx="14" ry="6" fill="#fff2c4"/></g>
                <g transform="translate(1000,0)"><polygon points="-46,150 46,150 190,640 -190,640" fill="url(#cone)"/><circle cx="0" cy="150" r="150" fill="url(#glow)"/><line x1="0" y1="0" x2="0" y2="112" stroke="#222" stroke-width="2"/><path d="M-30,112 L30,112 L48,146 L-48,146Z" fill="#e9a23b"/><ellipse cx="0" cy="148" rx="14" ry="6" fill="#fff2c4"/></g>
            </g>

            {{-- Drifting warm dust --}}
            <g fill="#ffd9a0">
                <circle class="dust" cx="180" cy="520" r="3" style="--d:-1s"/><circle class="dust" cx="420" cy="600" r="2.4" style="--d:-4s"/>
                <circle class="dust" cx="560" cy="480" r="3.2" style="--d:-7s"/><circle class="dust" cx="760" cy="560" r="2.2" style="--d:-2.5s"/>
                <circle class="dust" cx="940" cy="500" r="3" style="--d:-5.5s"/><circle class="dust" cx="1120" cy="590" r="2.6" style="--d:-8.5s"/>
                <circle class="dust" cx="1300" cy="520" r="3" style="--d:-3s"/><circle class="dust" cx="330" cy="470" r="2.2" style="--d:-9.5s"/>
            </g>
        </svg>

    @endif

    <div class="stage-overlay"></div>
</div>

<div class="layout">

    {{-- ================= Intro ================= --}}
    <section class="intro" aria-label="About EES Restaurant Manager">

        <a href="{{ url('/') }}" class="brand">
            <span class="brand-mark"><i class="fa-solid fa-utensils"></i></span>
            <span>
                <span class="brand-name">EES Restaurant Manager</span>
                <span class="brand-caption">by EES Technologies Ghana</span>
            </span>
        </a>

        <div>
            <div class="eyebrow">Simple · Reliable · Efficient</div>
            <p class="hero-title">Good Food.<br>Better <span>Management.</span></p>
            <p class="hero-text">
                Manage your restaurant's orders, payments, staff and daily operations
                from one place, so you can focus on serving your guests.
            </p>
            <div class="chips">
                <span class="chip"><i class="fa-solid fa-cart-shopping"></i> Orders</span>
                <span class="chip"><i class="fa-solid fa-money-bill-wave"></i> Payments</span>
                <span class="chip"><i class="fa-solid fa-receipt"></i> Receipts</span>
                <span class="chip"><i class="fa-solid fa-chart-column"></i> Sales reports</span>
            </div>
        </div>
    </section>

    {{-- ================= Login ================= --}}
    <main class="login">
        <div class="card-login">

            <a href="{{ url('/') }}" class="brand mobile-brand">
                <span class="brand-mark"><i class="fa-solid fa-utensils"></i></span>
                <span>
                    <span class="brand-name">EES Restaurant Manager</span>
                    <span class="brand-caption">by EES Technologies Ghana</span>
                </span>
            </a>

            <h1 class="heading">Welcome back</h1>
            <p class="subheading">Sign in with your staff account to continue.</p>

            @if (session('status'))
                <div class="notice success" role="status">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any() && ! $errors->has('email') && ! $errors->has('password'))
                <div class="notice error" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <div class="notice error" id="slowNotice" role="alert" hidden>
                <i class="fa-solid fa-hourglass-half"></i>
                <span>
                    This is taking longer than usual.
                    <button type="button" class="help-link" id="retryBtn">Cancel and try again</button>
                </span>
            </div>

            <form method="POST" action="{{ route('login.store') }}" id="loginForm" novalidate>
                @csrf

                <div class="field">
                    <label for="email" class="label">Email address</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-envelope input-icon" aria-hidden="true"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="you@example.com"
                            autocomplete="username"
                            inputmode="email"
                            required
                            autofocus
                            @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                        >
                    </div>
                    @error('email')
                        <div class="field-error" id="email-error" role="alert">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="password" class="label">Password</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock input-icon" aria-hidden="true"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control has-toggle @error('password') is-invalid @enderror"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                            @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                        >
                        <button type="button" class="toggle" id="togglePassword" aria-label="Show password" aria-pressed="false">
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="field-error" id="password-error" role="alert">{{ $message }}</div>
                    @enderror
                    <div class="caps-warning" id="capsWarning" role="status">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Caps Lock is on
                    </div>
                </div>

                <div class="options">
                    <div class="form-check">
                        <input type="checkbox" id="remember" name="remember" value="1" class="form-check-input" @checked(old('remember'))>
                        <label for="remember" class="form-check-label">Remember me</label>
                    </div>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="help-link">Forgot password?</a>
                    @else
                        <button type="button" class="help-link" id="helpToggle" aria-expanded="false" aria-controls="helpHint">
                            Need help signing in?
                        </button>
                    @endif
                </div>

                <div class="help-hint" id="helpHint" hidden>
                    Contact your system administrator to reset your password or recover your account.
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                    <span>Sign in to your account</span>
                </button>
            </form>

            <div class="secure">
                <div class="secure-icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></div>
                <div>
                    <div class="secure-title">Your account is protected</div>
                    <p class="secure-text">
                        Access is reserved for authorized restaurant staff. Never share your password with anyone.
                    </p>
                </div>
            </div>

            <footer class="legal">
                <strong>EES Technologies Ghana</strong>
                <div>&copy; {{ date('Y') }} EES Technologies Ghana. All rights reserved.</div>
            </footer>
        </div>
    </main>
</div>

<script>
    (function () {
        var password = document.getElementById('password');
        var toggle = document.getElementById('togglePassword');
        var caps = document.getElementById('capsWarning');
        var form = document.getElementById('loginForm');
        var submit = document.getElementById('submitBtn');
        var helpToggle = document.getElementById('helpToggle');
        var helpHint = document.getElementById('helpHint');
        var slowNotice = document.getElementById('slowNotice');
        var retryBtn = document.getElementById('retryBtn');
        var slowTimer;
        var video = document.getElementById('stageVideo');

        // Respect reduced-motion preference for the optional video background
        if (video && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            video.removeAttribute('autoplay');
            video.pause();
        }

        // Show / hide password
        toggle.addEventListener('click', function () {
            var reveal = password.type === 'password';
            password.type = reveal ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(reveal));
            toggle.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
            toggle.firstElementChild.className = reveal ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
        });

        // Caps Lock warning
        function checkCaps(e) {
            if (typeof e.getModifierState === 'function') {
                caps.classList.toggle('show', e.getModifierState('CapsLock'));
            }
        }
        password.addEventListener('keydown', checkCaps);
        password.addEventListener('keyup', checkCaps);
        password.addEventListener('blur', function () { caps.classList.remove('show'); });

        // Help hint (shown only when there is no password-reset route)
        if (helpToggle) {
            helpToggle.addEventListener('click', function () {
                var open = helpHint.hasAttribute('hidden');
                helpHint.toggleAttribute('hidden', !open);
                helpToggle.setAttribute('aria-expanded', String(open));
            });
        }

        // Prevent double submission
        var label = submit.querySelector('span');
        var icon = submit.querySelector('i');
        var defaultLabel = label.textContent;
        var defaultIcon = icon.className;

        form.addEventListener('submit', function () {
            if (!form.checkValidity()) { return; }
            submit.disabled = true;
            label.textContent = 'Signing in…';
            icon.className = 'fa-solid fa-spinner fa-spin';

            // Free up the CPU while the server works, and offer a way out if it stalls
            document.documentElement.classList.add('busy');
            clearTimeout(slowTimer);
            slowTimer = setTimeout(function () { slowNotice.hidden = false; }, 12000);
        });

        function resetSubmit() {
            clearTimeout(slowTimer);
            slowNotice.hidden = true;
            document.documentElement.classList.remove('busy');
            submit.disabled = false;
            label.textContent = defaultLabel;
            icon.className = defaultIcon;
        }

        retryBtn.addEventListener('click', function () {
            window.stop();   // cancel the pending request
            resetSubmit();
        });

        window.addEventListener('pageshow', function (e) {
            if (e.persisted) { resetSubmit(); }
        });
    })();
</script>
</body>
</html>