    {{-- ===========================================================
         BOUTIK CAMEROUN — Charte Graphique Teal (#0e7490)
         Page d'authentification : login / inscription / reset
         100 % local — aucune ressource externe (CDN ou Google Fonts).
         =========================================================== --}}

    <style>
        :root {
            --boutik-teal-50:  #ecfeff;
            --boutik-teal-100: #cffafe;
            --boutik-teal-200: #a5f3fc;
            --boutik-teal-400: #22d3ee;
            --boutik-teal-500: #06b6d4;
            --boutik-teal-600: #0891b2;
            --boutik-teal-700: #0e7490;  /* couleur primaire */
            --boutik-teal-800: #155e75;
            --boutik-teal-900: #164e63;
            --boutik-ink:      #0f172a;
            --boutik-ink-soft: #475569;
            --boutik-line:     #cbd5e1;
        }

        /* ---------- Fond d'écran ---------- */
        html {
            min-height: 100%;
            background:
                /* voile teal pour lisibilité du formulaire */
                linear-gradient(
                    135deg,
                    rgba(14, 116, 144, 0.78) 0%,
                    rgba(22, 78, 99, 0.85) 100%
                ),
                url("{{ asset('img/boutik/login-bg.png') }}") center / cover no-repeat fixed;
        }

        body {
            min-height: 100vh;
            background: transparent;
            margin: 0;
            padding: 0;
            font-family: 'Raleway', -apple-system, BlinkMacSystemFont,
                         'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: var(--boutik-ink);
        }

        /* ---------- Logo en haut à gauche ---------- */
        .boutik-brand-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .boutik-brand-logo img {
            width: 48px;
            height: 48px;
            max-width: 48px;
            max-height: 48px;
            object-fit: contain;
            flex-shrink: 0;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0,0,0,.18);
            background: transparent;
        }
        .boutik-brand-text {
            color: #fff;
            line-height: 1.1;
        }
        .boutik-brand-text .name {
            font-weight: 700;
            font-size: 22px;
            letter-spacing: .5px;
        }
        .boutik-brand-text .tag {
            font-size: 12px;
            opacity: .85;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }

        /* ---------- Wrapper centré (compense Tailwind absent en HTTP) ---------- */
        .boutik-auth-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 96px 16px 64px;
        }
        .boutik-auth-wrap .boutik-auth-card { width: 100%; max-width: 440px; }

        /* ---------- Card de connexion ---------- */
        .boutik-auth-card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow:
                0 20px 50px -10px rgba(15, 23, 42, .35),
                0 4px 12px rgba(15, 23, 42, .08);
            overflow: hidden;
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
        }
        .boutik-auth-card .card-head {
            background: linear-gradient(135deg, var(--boutik-teal-700), var(--boutik-teal-800));
            padding: 28px 24px 22px;
            text-align: center;
            color: #fff;
        }
        .boutik-auth-card .card-head .logo-circle {
            width: 88px;
            height: 88px;
            border-radius: 22px;
            background: #ffffff;
            box-shadow: 0 6px 18px rgba(0,0,0,.18);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            padding: 6px;
        }
        .boutik-auth-card .card-head .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .boutik-auth-card h1.card-title {
            margin: 0 0 4px;
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
        }
        .boutik-auth-card p.card-subtitle {
            margin: 0;
            font-size: 13px;
            color: rgba(255,255,255,.85);
        }

        /* ---------- Champs ---------- */
        .boutik-auth-card .field-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--boutik-ink);
            margin-bottom: 6px;
            display: block;
        }
        .boutik-auth-card .field-input {
            width: 100%;
            height: 46px;
            border: 1.5px solid var(--boutik-line);
            background: #f8fafc;
            border-radius: 10px;
            padding: 0 14px;
            font-size: 14px;
            color: var(--boutik-ink);
            outline: none;
            transition: all .18s ease;
        }
        .boutik-auth-card .field-input:focus {
            border-color: var(--boutik-teal-700);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(14,116,144,.18);
        }

        /* ---------- Bouton primaire ---------- */
        .boutik-btn-primary {
            display: block;
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--boutik-teal-700), var(--boutik-teal-800));
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: .3px;
            cursor: pointer;
            transition: transform .12s ease, box-shadow .15s ease, filter .15s ease;
            box-shadow: 0 6px 14px rgba(14,116,144,.35);
        }
        .boutik-btn-primary:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(14,116,144,.42);
        }
        .boutik-btn-primary:active {
            transform: translateY(0);
            filter: brightness(.95);
        }

        /* ---------- Liens ---------- */
        .boutik-link {
            color: var(--boutik-teal-700);
            font-weight: 600;
            text-decoration: none;
            transition: color .12s ease;
        }
        .boutik-link:hover {
            color: var(--boutik-teal-900);
            text-decoration: underline;
        }

        /* ---------- Bouton "S'inscrire" en haut ---------- */
        .boutik-topbar-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 18px;
            border: 2px solid rgba(255,255,255,.65);
            border-radius: 999px;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            backdrop-filter: blur(4px);
            background: rgba(255,255,255,.06);
            transition: all .15s ease;
        }
        .boutik-topbar-link:hover {
            background: #fff;
            color: var(--boutik-teal-700);
            border-color: #fff;
            text-decoration: none;
        }

        /* ---------- Sélecteur de langue ---------- */
        .boutik-lang-toggle {
            color: #ffffff !important;
            font-weight: 600;
        }
        .boutik-lang-toggle:hover {
            color: var(--boutik-teal-100) !important;
        }

        /* ---------- Pied de page (mention) ---------- */
        .boutik-footer-mention {
            position: fixed;
            bottom: 18px;
            left: 0;
            right: 0;
            text-align: center;
            color: rgba(255,255,255,.65);
            font-size: 12px;
            letter-spacing: .5px;
            pointer-events: none;
        }

        h1 { color: #fff; }
    </style>

    <style type="text/css">
        /* Pattern lock (legacy) — conservé pour compatibilité */
        .patt-wrap { z-index: 10; }
        .patt-circ.hovered { background-color: #cde2f2; border: none; }
        .patt-circ.hovered .patt-dots { display: none; }
        .patt-circ.dir {
            background-image: url("/boutik/img/icons/pattern-direction-icon-arrow.png");
            background-position: center;
            background-repeat: no-repeat;
        }
        .patt-circ.e   { transform: rotate(0); }
        .patt-circ.s-e { transform: rotate(45deg); }
        .patt-circ.s   { transform: rotate(90deg); }
        .patt-circ.s-w { transform: rotate(135deg); }
        .patt-circ.w   { transform: rotate(180deg); }
        .patt-circ.n-w { transform: rotate(225deg); }
        .patt-circ.n   { transform: rotate(270deg); }
        .patt-circ.n-e { transform: rotate(315deg); }
    </style>

<link href="{{ asset('css/tailwind/app.css') }}" rel="stylesheet">
