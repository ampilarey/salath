/**
 * Salat Prayer Times Widget
 * Drop-in vanilla JS widget for any website.
 *
 * Usage:
 *   <div id="prayer-widget" data-island-id="1" data-api-base="https://salat.yourdomain.mv"></div>
 *   <script src="https://salat.yourdomain.mv/widget.js"></script>
 *
 * Options (data attributes on the container):
 *   data-island-id  – initial island id (default: auto-detect via geolocation or Malé)
 *   data-api-base   – base URL of the salat API (default: same origin)
 *   data-theme      – "dark" (default) | "light"
 *   data-lang       – "dv" (default) | "en"
 */
(function (global) {
    'use strict';

    const CSS = `
.sw-wrap{font-family:'A_Faruma','MV Faseyha','MV Waheed',serif;direction:rtl;box-sizing:border-box}
.sw-wrap *{box-sizing:border-box}
.sw-wrap.sw-dark{--bg:#1e293b;--bg2:#263347;--border:#334155;--text:#f1f5f9;--muted:#94a3b8;--primary:#38bdf8;--accent:#f59e0b;--next-bg:rgba(56,189,248,.12)}
.sw-wrap.sw-light{--bg:#ffffff;--bg2:#f1f5f9;--border:#e2e8f0;--text:#0f172a;--muted:#64748b;--primary:#0284c7;--accent:#d97706;--next-bg:rgba(2,132,199,.08)}
.sw-wrap{background:var(--bg);border:1px solid var(--border);border-radius:14px;padding:1.25rem;color:var(--text)}
.sw-top{display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;margin-bottom:1rem}
.sw-select{flex:1;min-width:140px;background:var(--bg2);color:var(--text);border:1px solid var(--border);border-radius:8px;padding:.5rem .75rem;font-size:.9rem;font-family:inherit;outline:none;cursor:pointer}
.sw-select:focus{border-color:var(--primary)}
.sw-geo{background:transparent;color:var(--primary);border:1px solid var(--primary);border-radius:8px;padding:.5rem .75rem;font-size:.8rem;cursor:pointer;white-space:nowrap;font-family:inherit;transition:background .2s}
.sw-geo:hover{background:rgba(56,189,248,.12)}
.sw-countdown{background:var(--next-bg);border:1px solid rgba(56,189,248,.25);border-radius:10px;padding:.9rem 1rem;margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap}
.sw-next-label{font-size:.75rem;color:var(--muted);flex:0 0 100%;margin-bottom:.25rem}
.sw-next-name{font-size:1.1rem;font-weight:700;color:var(--primary)}
.sw-next-at{font-size:.8rem;color:var(--muted);margin-inline-start:.4rem}
.sw-timer{font-family:'Inter',monospace,sans-serif;font-size:1.4rem;font-weight:700;color:var(--accent);letter-spacing:.04em}
.sw-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.6rem}
@media(max-width:400px){.sw-grid{grid-template-columns:repeat(2,1fr)}}
.sw-card{background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:.7rem .8rem;display:flex;flex-direction:column;align-items:center;gap:.3rem;transition:border-color .2s;position:relative;overflow:hidden}
.sw-card.next{border-color:var(--primary);background:var(--next-bg)}
.sw-card.past{opacity:.5}
.sw-card-next-badge{position:absolute;top:.3rem;left:.4rem;font-size:.6rem;color:var(--primary);background:rgba(56,189,248,.15);padding:.1rem .35rem;border-radius:4px;font-weight:600}
.sw-icon{font-size:1.35rem;line-height:1}
.sw-name{font-size:.78rem;font-weight:600;color:var(--text);text-align:center}
.sw-latin{font-size:.65rem;color:var(--muted);font-family:'Inter',sans-serif}
.sw-time{font-size:1.1rem;font-weight:700;color:var(--text);font-family:'Inter',monospace,sans-serif;letter-spacing:.02em}
.sw-card.next .sw-time{color:var(--primary)}
.sw-loading{text-align:center;padding:2rem;color:var(--muted);font-size:.9rem}
.sw-error{text-align:center;padding:1rem;color:#f87171;font-size:.85rem}
`;

    const PRAYER_META = {
        fajr:    { dv: 'ފަތިސް',     en: 'Fajr',    icon: '🌙' },
        sunrise: { dv: 'އިރު ނެގުން', en: 'Sunrise', icon: '🌅' },
        dhuhr:   { dv: 'މެންދުރު',   en: 'Dhuhr',   icon: '☀️' },
        asr:     { dv: 'އަޞްރު',     en: 'Asr',     icon: '🌤' },
        maghrib: { dv: 'މަޣްރިބް',   en: 'Maghrib', icon: '🌆' },
        isha:    { dv: 'ޢިޝާ',      en: 'Isha',    icon: '🌟' },
    };

    const KEYS = ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'];

    function injectStyles() {
        if (document.getElementById('sw-styles')) return;
        const style = document.createElement('style');
        style.id = 'sw-styles';
        style.textContent = CSS;
        document.head.appendChild(style);
        // Load A_Faruma font
        const fontFace = document.createElement('style');
        fontFace.textContent = "@font-face{font-family:'A_Faruma';src:url('https://salath.bakeandgrill.mv/fonts/a_faruma.ttf') format('truetype');font-display:swap;}";
        document.head.appendChild(fontFace);
    }

    function hhmm(m) {
        m = ((m % 1440) + 1440) % 1440;
        return String(Math.floor(m / 60)).padStart(2, '0') + ':' + String(m % 60).padStart(2, '0');
    }

    function parseHHMM(s) {
        const [h, m] = s.split(':').map(Number);
        return h * 60 + m;
    }

    function formatCountdown(ms) {
        const s   = Math.max(0, Math.floor(ms / 1000));
        const h   = Math.floor(s / 3600);
        const min = Math.floor((s % 3600) / 60);
        const sec = s % 60;
        return [h, min, sec].map(v => String(v).padStart(2, '0')).join(':');
    }

    function Widget(container) {
        const apiBase = (container.dataset.apiBase || '').replace(/\/$/, '');
        const theme   = container.dataset.theme === 'light' ? 'light' : 'dark';
        const lang    = container.dataset.lang   === 'en'   ? 'en'    : 'dv';
        let   islandId = parseInt(container.dataset.islandId, 10) || 0;
        let   prayerData = null;
        let   islands    = [];
        let   timer      = null;

        /* ── DOM skeleton ── */
        container.innerHTML = '<div class="sw-loading">ލޯޑު ވަނީ...</div>';
        container.className = `sw-wrap sw-${theme}`;

        function render(prayers) {
            prayerData = prayers;
            const isToday = new Date().toISOString().slice(0, 10) === new Date().toISOString().slice(0, 10);

            container.innerHTML = `
                <div class="sw-top">
                    <select class="sw-select" id="sw-sel-${container.id}"></select>
                    <button class="sw-geo" id="sw-geo-${container.id}">
                        📍 ${lang === 'dv' ? 'ތިބާ ހުރި ތަން' : 'My location'}
                    </button>
                </div>
                <div class="sw-countdown" id="sw-cd-${container.id}">
                    <div class="sw-next-label">${lang === 'dv' ? 'ދެން ވަންނަ ނަމާދު' : 'Next prayer'}</div>
                    <div>
                        <span class="sw-next-name" id="sw-nn-${container.id}">–</span>
                        <span class="sw-next-at"   id="sw-nt-${container.id}"></span>
                    </div>
                    <div class="sw-timer" id="sw-tm-${container.id}">––:––:––</div>
                </div>
                <div class="sw-grid" id="sw-grid-${container.id}"></div>
            `;

            /* populate select */
            const sel = document.getElementById(`sw-sel-${container.id}`);
            const groups = {};
            islands.forEach(isl => {
                if (!groups[isl.atoll]) groups[isl.atoll] = [];
                groups[isl.atoll].push(isl);
            });
            Object.entries(groups).forEach(([atoll, list]) => {
                const og = document.createElement('optgroup');
                og.label = atoll;
                list.forEach(isl => {
                    const opt = document.createElement('option');
                    opt.value = isl.id;
                    opt.textContent = isl.name;
                    if (isl.id === islandId) opt.selected = true;
                    og.appendChild(opt);
                });
                sel.appendChild(og);
            });
            sel.addEventListener('change', () => {
                islandId = parseInt(sel.value, 10);
                loadTimes();
            });

            /* geo button */
            document.getElementById(`sw-geo-${container.id}`).addEventListener('click', geoLocate);

            /* prayer cards */
            const grid = document.getElementById(`sw-grid-${container.id}`);
            KEYS.forEach(key => {
                const div = document.createElement('div');
                div.className = 'sw-card';
                div.dataset.key  = key;
                div.dataset.time = prayers[key];
                div.innerHTML = `
                    <div class="sw-icon">${PRAYER_META[key].icon}</div>
                    <div class="sw-name">${PRAYER_META[key][lang]}</div>
                    <div class="sw-latin">${PRAYER_META[key].en}</div>
                    <div class="sw-time">${prayers[key]}</div>
                `;
                grid.appendChild(div);
            });

            if (timer) clearInterval(timer);
            tick();
            timer = setInterval(tick, 1000);
        }

        function tick() {
            if (!prayerData) return;
            const now    = new Date();
            const nowMin = now.getHours() * 60 + now.getMinutes();

            let nextKey  = null;
            let nextTime = null;
            const cards  = container.querySelectorAll('.sw-card');
            cards.forEach(card => {
                card.classList.remove('next', 'past');
                card.querySelector('.sw-card-next-badge')?.remove();
                const t = parseHHMM(card.dataset.time);
                if (!nextKey && t > nowMin) {
                    nextKey  = card.dataset.key;
                    nextTime = card.dataset.time;
                    card.classList.add('next');
                    const badge = document.createElement('span');
                    badge.className = 'sw-card-next-badge';
                    badge.textContent = lang === 'dv' ? 'ދެން' : 'next';
                    card.prepend(badge);
                } else if (t <= nowMin) {
                    card.classList.add('past');
                }
            });

            const nn = document.getElementById(`sw-nn-${container.id}`);
            const nt = document.getElementById(`sw-nt-${container.id}`);
            const tm = document.getElementById(`sw-tm-${container.id}`);
            if (!nn) return;

            if (nextKey) {
                nn.textContent = PRAYER_META[nextKey][lang];
                nt.textContent = nextTime;
                const [nh, nm] = nextTime.split(':').map(Number);
                const target = new Date(); target.setHours(nh, nm, 0, 0);
                tm.textContent = formatCountdown(target - now);
            } else {
                nn.textContent = lang === 'dv' ? 'ދެން ވަންނަ ނަމާދު ނެތް' : 'No more prayers today';
                nt.textContent = '';
                tm.textContent = '––:––:––';
            }
        }

        function loadTimes() {
            const today = new Date().toISOString().slice(0, 10);
            fetch(`${apiBase}/api/prayer-times?island_id=${islandId}&date=${today}`)
                .then(r => {
                    if (!r.ok) throw new Error(r.statusText);
                    return r.json();
                })
                .then(data => render(data.prayers))
                .catch(err => {
                    container.innerHTML = `<div class="sw-error">ލޯޑު ނުވި. ${err.message}</div>`;
                });
        }

        function geoLocate() {
            if (!navigator.geolocation) return;
            fetch(`${apiBase}/api/prayer-times/nearest?lat=0&lng=0`) // preflight test
                .catch(() => {});
            navigator.geolocation.getCurrentPosition(pos => {
                const { latitude, longitude } = pos.coords;
                fetch(`${apiBase}/api/prayer-times/nearest?lat=${latitude}&lng=${longitude}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.island) {
                            islandId = data.island.id;
                            loadTimes();
                        }
                    });
            });
        }

        /* ── Boot ── */
        fetch(`${apiBase}/api/prayer-times/islands`)
            .then(r => r.json())
            .then(data => {
                islands = data.islands || [];
                if (!islandId && islands.length) {
                    // Default to Malé or first island
                    const male = islands.find(i => i.name === 'މާލެ') || islands[0];
                    islandId = male.id;
                }
                loadTimes();
            })
            .catch(() => {
                container.innerHTML = '<div class="sw-error">ސާވާ ހޯދިއެއް ނުގެ.</div>';
            });
    }

    /* ── Auto-init all [data-salat-widget] containers ── */
    function init() {
        injectStyles();
        document.querySelectorAll('[data-salat-widget]').forEach((el, i) => {
            if (!el.id) el.id = 'sw-' + i;
            new Widget(el);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    /* Expose for manual init */
    global.SalatWidget = Widget;

})(window);
