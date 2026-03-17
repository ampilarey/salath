<!DOCTYPE html>
<html lang="dv" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'ދިވެހިރާއްޖޭގެ ހުރިހާ ރަށެއްގެ ނަމާދު ވަގުތު')">
    <title>@yield('title', 'ނަމާދު ވަގުތު – ދިވެހިރާއްޖެ')</title>

    {{-- Self-hosted Dhivehi font --}}
    <style>
        @font-face {
            font-family: 'A_Faruma';
            src: url('/fonts/a_faruma.ttf') format('truetype');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
    </style>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ═══════════════ Design tokens ═══════════════ */
        :root {
            /* Palette — warm parchment + deep emerald + lagoon + gold */
            --bg:          #F7F1E3;
            --surface:     #FFFDF8;
            --surface-2:   #F0E8D5;
            --primary:     #0F5C4D;
            --primary-l:   #1A7A65;
            --teal:        #0E7490;
            --gold:        #C9A227;
            --gold-l:      #E8C547;
            --ink:         #1F1A17;
            --muted:       #7C6E58;
            --border:      #E8DDC5;
            --border-strong:#C4B49A;
            --next-bg:     rgba(15,92,77,0.06);
            --next-border:  rgba(15,92,77,0.25);
            --error:       #A61E2D;

            /* Spacing / shape */
            --radius:      16px;
            --radius-sm:   10px;
            --radius-xs:   6px;

            /* Typography */
            --font-dhivehi: 'A_Faruma', 'MV Faseyha', 'MV Waheed', serif;
            --font-latin:   'Crimson Pro', Georgia, serif;
            --font-body:    'Crimson Pro', Georgia, serif;
        }

        html { font-size: 16px; scroll-behavior: smooth; }

        body {
            background: var(--bg);
            color: var(--ink);
            font-family: var(--font-dhivehi);
            min-height: 100vh;
            line-height: 1.6;
        }

        a { color: var(--primary); text-decoration: none; }
        a:hover { text-decoration: underline; }

        .container { max-width: 1060px; margin: 0 auto; padding: 0 1.25rem; }

        /* ── Site Header ── */
        .site-header {
            background: var(--surface);
            border-bottom: 3px solid var(--gold);
            padding: 1rem 0;
            position: relative;
            overflow: hidden;
        }

        /* Subtle geometric background pattern */
        .site-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(201,162,39,0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(15,92,77,0.05) 0%, transparent 50%);
            pointer-events: none;
        }

        .site-header .inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        .site-logo {
            display: flex;
            align-items: center;
            gap: .75rem;
            text-decoration: none;
        }

        .site-logo-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .site-logo-icon svg {
            width: 22px;
            height: 22px;
            fill: var(--gold);
        }

        .site-logo-text {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .site-logo-dv {
            font-family: var(--font-dhivehi);
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1.2;
        }

        .site-logo-latin {
            font-family: var(--font-latin);
            font-size: .82rem;
            color: var(--muted);
            font-weight: 500;
            letter-spacing: .04em;
            direction: ltr;
        }

        /* ── Footer ── */
        .site-footer {
            background: var(--surface);
            border-top: 1px solid var(--border);
            text-align: center;
            padding: 1.5rem 0;
            color: var(--muted);
            font-size: .88rem;
            margin-top: 4rem;
            font-family: var(--font-body);
            direction: ltr;
        }

        .site-footer strong {
            color: var(--primary);
            font-weight: 600;
        }

        @yield('extra-styles')
    </style>
    @yield('head')
</head>
<body>

<header class="site-header">
    <div class="container inner">
        <a href="{{ route('prayer-times.index') }}" class="site-logo">
            <div class="site-logo-icon">
                {{-- Crescent moon SVG --}}
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </div>
            <div class="site-logo-text">
                <span class="site-logo-dv">ނަމާދު ވަގުތު</span>
                <span class="site-logo-latin">Maldives Prayer Times</span>
            </div>
        </a>
    </div>
</header>

<main>
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container">
        <p>
            <strong>Salath</strong> &mdash;
            Maldives Prayer Times for all islands &mdash;
            {{ date('Y') }}
        </p>
    </div>
</footer>

@yield('scripts')
</body>
</html>
