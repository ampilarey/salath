@extends('layout')

@section('title', 'ނަމާދު ވަގުތު – ' . ($selectedIsland?->name ?? 'ދިވެހިރާއްޖެ'))

@section('extra-styles')
<style>
/* ═══════════════════════ Page layout ═══════════════════════ */
.pt-page { padding: 2rem 0 4rem; }

/* ─── Hero / island + date bar ─── */
.pt-controls {
    background: var(--clr-surface);
    border: 1px solid var(--clr-border);
    border-radius: var(--radius);
    padding: 1.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
    margin-bottom: 2rem;
}
.pt-controls label {
    display: block;
    font-size: .78rem;
    color: var(--clr-muted);
    margin-bottom: .4rem;
    letter-spacing: .03em;
}
.pt-controls select,
.pt-controls input[type="date"] {
    background: var(--clr-surface2);
    color: var(--clr-text);
    border: 1px solid var(--clr-border);
    border-radius: var(--radius-sm);
    padding: .6rem .9rem;
    font-size: .95rem;
    font-family: var(--font-dhivehi);
    cursor: pointer;
    outline: none;
    transition: border-color .2s;
    width: 100%;
}
.pt-controls select:focus,
.pt-controls input[type="date"]:focus { border-color: var(--clr-primary); }
.pt-field { flex: 1 1 220px; }
.pt-field-date { flex: 0 0 160px; }
.pt-geo-btn {
    background: var(--clr-surface2);
    color: var(--clr-primary);
    border: 1px solid var(--clr-primary);
    border-radius: var(--radius-sm);
    padding: .6rem 1rem;
    font-size: .88rem;
    cursor: pointer;
    white-space: nowrap;
    transition: background .2s;
    display: flex;
    align-items: center;
    gap: .4rem;
    font-family: var(--font-dhivehi);
}
.pt-geo-btn:hover { background: rgba(56,189,248,.15); }
.pt-geo-btn svg { width: 16px; height: 16px; flex-shrink: 0; }

/* ─── Hijri / Gregorian date strip ─── */
.pt-date-strip {
    text-align: center;
    margin-bottom: 2rem;
}
.pt-date-strip .pt-greg {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--clr-text);
}
.pt-date-strip .pt-hijri {
    font-size: .88rem;
    color: var(--clr-muted);
    margin-top: .2rem;
}
.pt-island-name {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--clr-primary);
    margin-bottom: .25rem;
}
.pt-maldives-clock {
    font-size: 2rem;
    font-weight: 700;
    color: var(--clr-text);
    font-family: 'Inter', monospace;
    letter-spacing: .06em;
    margin-top: .4rem;
}
.pt-maldives-clock span {
    font-size: .75rem;
    color: var(--clr-muted);
    font-weight: 400;
    letter-spacing: .03em;
    margin-right: .3rem;
}

/* ─── Countdown ─── */
.pt-countdown {
    background: var(--clr-next-bg);
    border: 1px solid rgba(56,189,248,.3);
    border-radius: var(--radius);
    padding: 1.25rem 1.5rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.pt-countdown-label {
    color: var(--clr-muted);
    font-size: .85rem;
    flex: 0 0 100%;
}
.pt-countdown-prayer {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--clr-primary);
}
.pt-countdown-time {
    font-size: .95rem;
    color: var(--clr-muted);
    margin-inline-start: .5rem;
}
.pt-countdown-remaining {
    margin-inline-start: auto;
    font-size: 2rem;
    font-weight: 700;
    color: var(--clr-accent);
    font-family: 'Inter', monospace;
    letter-spacing: .04em;
}

/* ─── Prayer grid ─── */
.pt-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}
.pt-card {
    background: var(--clr-surface);
    border: 1px solid var(--clr-border);
    border-radius: var(--radius);
    padding: 1.4rem 1.6rem;
    display: flex;
    align-items: center;
    gap: 1.2rem;
    transition: border-color .2s, transform .15s;
    position: relative;
    overflow: hidden;
}
.pt-card:hover { border-color: var(--clr-primary); transform: translateY(-2px); }
.pt-card.is-next {
    border-color: var(--clr-primary);
    background: var(--clr-next-bg);
}
.pt-card.is-next::before {
    content: 'ދެން';
    position: absolute;
    top: .5rem;
    left: .75rem;
    font-size: .68rem;
    color: var(--clr-primary);
    background: rgba(56,189,248,.15);
    padding: .15rem .4rem;
    border-radius: 4px;
    font-weight: 600;
}
.pt-card.is-past { opacity: .55; }
.pt-card-icon {
    width: 46px;
    height: 46px;
    background: var(--clr-surface2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.4rem;
}
.pt-card.is-next .pt-card-icon { background: rgba(56,189,248,.2); }
.pt-card-body { flex: 1; }
.pt-card-name {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--clr-text);
}
.pt-card-sub { font-size: .8rem; color: var(--clr-muted); margin-top: .1rem; }
.pt-card-time {
    font-size: 1.55rem;
    font-weight: 700;
    color: var(--clr-text);
    font-family: 'Inter', system-ui, sans-serif;
    letter-spacing: .02em;
}
.pt-card.is-next .pt-card-time { color: var(--clr-primary); }

/* ─── No-data ─── */
.pt-empty {
    text-align: center;
    padding: 4rem 1rem;
    color: var(--clr-muted);
}
.pt-empty svg { width: 64px; height: 64px; opacity: .3; margin-bottom: 1rem; }
</style>
@endsection

@section('content')
<div class="pt-page">
<div class="container">

    {{-- ══ Controls ══ --}}
    <form id="ptForm" method="GET" action="/prayer-times">
        <div class="pt-controls">
            {{-- Island selector --}}
            <div class="pt-field">
                <label for="island_id">ރަށް</label>
                <select name="island_id" id="island_id">
                    @foreach($grouped as $atoll => $atollIslands)
                        <optgroup label="{{ $atoll }}">
                            @foreach($atollIslands as $isl)
                                <option value="{{ $isl->id }}"
                                    data-lat="{{ $isl->latitude }}"
                                    data-lng="{{ $isl->longitude }}"
                                    {{ (int)$isl->id === (int)($selectedIsland?->id ?? 0) ? 'selected' : '' }}>
                                    {{ $isl->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            {{-- Date picker --}}
            <div class="pt-field-date">
                <label for="date">ތާރީޙް</label>
                <input type="date" name="date" id="date"
                       value="{{ $selectedDate->toDateString() }}"
                       min="2026-01-01"
                       max="2026-12-31">
            </div>

            {{-- Geolocation button --}}
            <button type="button" class="pt-geo-btn" id="geoBtn" title="ތިބާ ހުރި ތަން ހޯދުން">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/>
                    <circle cx="12" cy="12" r="9" opacity=".3"/>
                </svg>
                ތިބާ ހުރި ތަން
            </button>
        </div>
    </form>

    {{-- ══ Date heading ══ --}}
    <div class="pt-date-strip">
        <div class="pt-island-name">{{ $selectedIsland?->name ?? '–' }}</div>
        <div class="pt-greg">{{ $selectedDate->translatedFormat('l, j F Y') }}</div>
        <div class="pt-hijri" id="hijriDate">ލޯޑު ވަނީ...</div>
        <div class="pt-maldives-clock" id="maldivesClock">––:––:–– </div>
    </div>

    @if($prayers)
        {{-- ══ Countdown ══ --}}
        <div class="pt-countdown" id="countdownBox">
            <div class="pt-countdown-label">ދެން ވަންނަ ނަމާދު</div>
            <div>
                <span class="pt-countdown-prayer" id="nextPrayerName">–</span>
                <span class="pt-countdown-time" id="nextPrayerTime"></span>
            </div>
            <div class="pt-countdown-remaining" id="countdownTimer">––:––:––</div>
        </div>

        {{-- ══ Prayer cards ══ --}}
        <div class="pt-grid" id="prayerGrid">
            @php
                $prayerDefs = [
                    'fajr'    => ['name' => 'ފަތިސް',  'latin' => 'Fajr',    'icon' => '🌙'],
                    'sunrise' => ['name' => 'އިރު އެރުން', 'latin' => 'Sunrise', 'icon' => '🌅'],
                    'dhuhr'   => ['name' => 'މެންދުރު', 'latin' => 'Dhuhr',   'icon' => '☀️'],
                    'asr'     => ['name' => 'އަޞްރު',  'latin' => 'Asr',     'icon' => '🌤️'],
                    'maghrib' => ['name' => 'މަޣްރިބް', 'latin' => 'Maghrib', 'icon' => '🌆'],
                    'isha'    => ['name' => 'ޢިޝާ',   'latin' => 'Isha',    'icon' => '🌟'],
                ];
            @endphp

            @foreach($prayerDefs as $key => $def)
                <div class="pt-card" data-prayer="{{ $key }}" data-time="{{ $prayers[$key] }}">
                    <div class="pt-card-icon">{{ $def['icon'] }}</div>
                    <div class="pt-card-body">
                        <div class="pt-card-name">{{ $def['name'] }}</div>
                        <div class="pt-card-sub">{{ $def['latin'] }}</div>
                    </div>
                    <div class="pt-card-time">{{ $prayers[$key] }}</div>
                </div>
            @endforeach
        </div>
    @else
        <div class="pt-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
            </svg>
            <p>މި ތާރީޙަށް ނަމާދު ވަގުތު ހޯދިއެއް ނުގެ.</p>
        </div>
    @endif

</div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    'use strict';

    /* ── Prayer times from server ── */
    const PRAYERS_RAW = @json($prayers ?? []);
    const IS_TODAY = '{{ $selectedDate->toDateString() }}' === new Date().toISOString().slice(0,10);

    /* ─────────────── Auto-submit on change ─────────────── */
    document.getElementById('island_id').addEventListener('change', () => document.getElementById('ptForm').submit());
    document.getElementById('date').addEventListener('change',      () => document.getElementById('ptForm').submit());

    /* ─────────────── Geolocation ─────────────── */
    document.getElementById('geoBtn').addEventListener('click', function () {
        if (!navigator.geolocation) { alert('ތިބާ ހުރި ތަން ހޯދޭކަށް ނެތް.'); return; }
        this.textContent = 'ހޯދަނީ...';
        this.disabled = true;
        const btn = this;
        navigator.geolocation.getCurrentPosition(
            pos => {
                const { latitude, longitude } = pos.coords;
                fetch(`/api/prayer-times/nearest?lat=${latitude}&lng=${longitude}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.island) {
                            document.getElementById('island_id').value = data.island.id;
                            document.getElementById('ptForm').submit();
                        }
                    })
                    .catch(() => { btn.textContent = 'ތިބާ ހުރި ތަން'; btn.disabled = false; });
            },
            () => { btn.textContent = 'ތިބާ ހުރި ތަން'; btn.disabled = false; }
        );
    });

    /* ─────────────── Hijri date ─────────────── */
    (function setHijri() {
        try {
            const parts = new Intl.DateTimeFormat('ar-SA-u-ca-islamic', {
                day: 'numeric', month: 'long', year: 'numeric'
            }).formatToParts(new Date('{{ $selectedDate->toDateString() }}'));
            const hijri = parts.map(p => p.value).join('');
            document.getElementById('hijriDate').textContent = hijri;
        } catch (e) {
            document.getElementById('hijriDate').textContent = '';
        }
    })();

    /* ─────────────── Maldives live clock (UTC+5) ─────────────── */
    (function maldivesClock() {
        function updateClock() {
            const now = new Date();
            const mv = new Date(now.toLocaleString('en-US', { timeZone: 'Indian/Maldives' }));
            const h = String(mv.getHours()).padStart(2, '0');
            const m = String(mv.getMinutes()).padStart(2, '0');
            const s = String(mv.getSeconds()).padStart(2, '0');
            const ampm = mv.getHours() >= 12 ? 'PM' : 'AM';
            document.getElementById('maldivesClock').innerHTML =
                `<span>ރާއްޖެ ގަޑި</span>${h}:${m}:${s} <small style="font-size:.8rem;color:var(--clr-muted)">${ampm}</small>`;
        }
        updateClock();
        setInterval(updateClock, 1000);
    })();

    /* ─────────────── Next-prayer highlight & countdown ─────────────── */
    if (!PRAYERS_RAW || Object.keys(PRAYERS_RAW).length === 0) return;

    const PRAYER_NAMES_DV = {
        fajr: 'ފަތިސް', dhuhr: 'މެންދުރު',
        asr: 'އަޞްރު', maghrib: 'މަޣްރިބް', isha: 'ޢިޝާ'
    };

    // Prayers to skip in countdown (sunrise is not a prayer)
    const SKIP_COUNTDOWN = ['sunrise'];

    function parseHHMM(s) {
        const [h, m] = s.split(':').map(Number);
        return h * 60 + m;
    }

    function findNextPrayer() {
        const now = new Date();
        const nowMin = now.getHours() * 60 + now.getMinutes();
        const entries = Object.entries(PRAYERS_RAW);
        for (const [key, time] of entries) {
            if (SKIP_COUNTDOWN.includes(key)) continue;
            if (parseHHMM(time) > nowMin) return { key, time };
        }
        return null; // all prayers done today
    }

    function formatCountdown(diffMs) {
        const totalSec = Math.floor(diffMs / 1000);
        const h = Math.floor(totalSec / 3600);
        const m = Math.floor((totalSec % 3600) / 60);
        const s = totalSec % 60;
        return [h, m, s].map(v => String(v).padStart(2, '0')).join(':');
    }

    function tick() {
        const cards = document.querySelectorAll('.pt-card');
        const now   = new Date();
        const nowMin = now.getHours() * 60 + now.getMinutes();

        // Mark past / next
        let nextFound = false;
        cards.forEach(card => {
            const t = parseHHMM(card.dataset.time);
            card.classList.remove('is-next', 'is-past');
            if (!nextFound && t > nowMin) {
                card.classList.add('is-next');
                nextFound = true;
            } else if (t <= nowMin) {
                card.classList.add('is-past');
            }
        });

        // Countdown
        const next = findNextPrayer();
        if (next) {
            document.getElementById('nextPrayerName').textContent = PRAYER_NAMES_DV[next.key] ?? next.key;
            document.getElementById('nextPrayerTime').textContent = next.time;

            const [nh, nm] = next.time.split(':').map(Number);
            const target = new Date();
            target.setHours(nh, nm, 0, 0);
            const diff = target - now;
            document.getElementById('countdownTimer').textContent = diff > 0 ? formatCountdown(diff) : '00:00:00';
        } else {
            document.getElementById('countdownBox').style.opacity = '.5';
            document.getElementById('nextPrayerName').textContent = 'ދެން ވަންނަ ނަމާދު ނެތް';
            document.getElementById('countdownTimer').textContent = '––:––:––';
        }
    }

    if (IS_TODAY) {
        tick();
        setInterval(tick, 1000);
    }
})();
</script>
@endsection
