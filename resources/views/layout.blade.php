<!DOCTYPE html>
<html lang="dv" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'ދިވެހިރާއްޖޭގެ ހުރިހާ ރަށެއްގެ ނަމާދު ވަގުތު')">
    <title>@yield('title', 'ނަމާދު ވަގުތު – ދިވެހިރާއްޖެ')</title>

    <style>
        @font-face {
            font-family: 'A_Faruma';
            src: url('/fonts/A_Faruma.woff2') format('woff2'),
                 url('/fonts/A_Faruma.woff') format('woff'),
                 url('/fonts/A_Faruma.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
    </style>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --clr-bg:        #0f172a;
            --clr-surface:   #1e293b;
            --clr-surface2:  #263347;
            --clr-border:    #334155;
            --clr-primary:   #38bdf8;
            --clr-primary-d: #0ea5e9;
            --clr-accent:    #f59e0b;
            --clr-text:      #f1f5f9;
            --clr-muted:     #94a3b8;
            --clr-green:     #22c55e;
            --clr-next-bg:   rgba(56,189,248,.12);
            --radius:        14px;
            --radius-sm:     8px;
            --font-latin:    'Inter', system-ui, sans-serif;
            --font-dhivehi:  'A_Faruma', 'MV Faseyha', 'MV Waheed', serif;
        }

        html { font-size: 16px; scroll-behavior: smooth; }

        body {
            background: var(--clr-bg);
            color: var(--clr-text);
            font-family: var(--font-dhivehi);
            min-height: 100vh;
        }

        a { color: var(--clr-primary); text-decoration: none; }
        a:hover { text-decoration: underline; }

        .container { max-width: 1040px; margin: 0 auto; padding: 0 1rem; }

        /* ── Header ── */
        .site-header {
            background: var(--clr-surface);
            border-bottom: 1px solid var(--clr-border);
            padding: .875rem 0;
        }
        .site-header .inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .site-logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--clr-primary);
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .site-logo svg { width: 28px; height: 28px; }

        /* ── Footer ── */
        .site-footer {
            background: var(--clr-surface);
            border-top: 1px solid var(--clr-border);
            text-align: center;
            padding: 1.25rem 0;
            color: var(--clr-muted);
            font-size: .85rem;
            margin-top: 3rem;
        }

        @yield('extra-styles')
    </style>
    @yield('head')
</head>
<body>

<header class="site-header">
    <div class="container inner">
        <a href="/" class="site-logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            ނަމާދު ވަގުތު
        </a>
    </div>
</header>

<main>
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container">
        <p>ދިވެހިރާއްޖޭގެ ހުރިހާ ރަށެއްގެ ނަމާދު ވަގުތު &mdash; {{ date('Y') }}</p>
    </div>
</footer>

@yield('scripts')
</body>
</html>
