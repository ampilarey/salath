# Salath — Maldives Prayer Times

A production-grade Laravel application providing prayer times for every inhabited island in the Maldives. Bilingual (Dhivehi / Latin), culturally grounded, mobile-first, and embeddable.

---

## What it does

- Shows the six daily prayer times (Fajr, Sunrise, Dhuhr, Asr, Maghrib, Isha) for any island in the Maldives
- Live countdown to the next prayer
- Live Maldives clock (UTC +5)
- Bilingual island names — Dhivehi (Thaana script) and Latin
- Hijri date alongside the Gregorian date
- Geolocation — automatically finds the nearest island
- Embeddable widget for any third-party website
- REST API for programmatic access

---

## Requirements

| Dependency | Minimum |
|---|---|
| PHP | 8.2 |
| Laravel | 12.x |
| MySQL / MariaDB | 5.7 / 10.3 |
| Composer | 2.x |
| Node.js | 18+ (for `npm run build`) |

SQLite is supported for local development only. **Production requires MySQL/MariaDB.**

---

## Architecture

```
app/
├── Console/Commands/
│   ├── AddLatinNames.php          # Enrich islands with Latin name data
│   ├── PrayerImport.php           # Wrapper: import → enrich → cache-clear
│   └── PrayerClearCache.php       # Bust prayer island/times cache
│
├── Domains/PrayerTimes/           # Domain logic — no HTTP concerns here
│   ├── Actions/
│   │   ├── FindNearestIsland.php
│   │   ├── GetIslandCollection.php         # Cached (1 hour)
│   │   └── GetPrayerTimesForIslandAndDate.php # Cached (24 hours)
│   ├── DTOs/
│   │   ├── IslandData.php
│   │   └── PrayerTimesResult.php
│   ├── Services/
│   │   ├── NearestIslandFinder.php  # Haversine via SQL
│   │   └── PrayerTimeResolver.php   # Core calculation (offset, day-of-year)
│   └── ViewModels/
│       └── PrayerPageViewModel.php  # Everything the Blade view needs
│
├── Http/
│   ├── Controllers/
│   │   ├── PrayerTimesWebController.php   # Thin — validate → Action → ViewModel
│   │   └── Api/
│   │       ├── IslandsController.php
│   │       ├── NearestIslandController.php
│   │       └── PrayerTimesApiController.php
│   ├── Requests/
│   │   ├── NearestIslandRequest.php
│   │   ├── PrayerTimesApiRequest.php
│   │   └── PrayerTimesWebRequest.php
│   └── Resources/
│       ├── IslandResource.php
│       └── PrayerTimesResource.php
│
└── Support/
    └── PrayerTimeHelper.php       # minutesToTime, dayOfYear, parseDate

database/
├── migrations/
│   ├── ..._create_prayer_tables.php
│   └── ..._add_latin_names_to_prayer_islands.php
└── seeders/
    └── PrayerTimesSeeder.php      # Imports from salat.db SQLite source

resources/views/
├── layout.blade.php               # Design system, fonts, header/footer
├── prayer-times.blade.php         # Main view using ViewModel + components
└── components/
    ├── island-picker.blade.php    # Custom searchable dropdown (RTL-aware)
    └── date-picker.blade.php      # Styled date control

public/
└── widget.js                      # Drop-in embeddable widget (vanilla JS)
```

### Design principles

- **Controllers are thin** — validate input, call one Action, return view or resource.
- **Actions coordinate** — they call Services and return DTOs; they own the caching layer.
- **Services are pure** — no HTTP or cache knowledge; only domain logic.
- **Views consume ViewModels** — no raw DB objects, no logic, only pre-shaped data.

---

## Local Setup

### 1. Clone and install

```bash
git clone git@github.com:ampilarey/salath.git
cd salath
composer install
npm install && npm run build
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials and prayer source DB path:

```env
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=salath
DB_USERNAME=root
DB_PASSWORD=secret

# Path to the salat.db SQLite source file:
PRAYER_TIMES_DB=/path/to/salat.db
```

> For **local SQLite** development, set `DB_CONNECTION=sqlite` and `DB_DATABASE=database/database.sqlite` instead of MySQL.

### 3. Run migrations

```bash
php artisan migrate
```

### 4. Import prayer data

Copy `salat.db` to the path set in `PRAYER_TIMES_DB`, then:

```bash
php artisan prayer:import
```

This runs the seeder, enriches islands with Latin names, and clears stale cache.

Alternatively, supply the path directly:

```bash
php artisan prayer:import --path=/absolute/path/to/salat.db
```

### 5. Serve

```bash
php artisan serve
```

Open `http://localhost:8000` — you should see the prayer times interface with all islands loaded.

---

## Dhivehi Font

The A_Faruma font (`.ttf`) must be placed at `public/fonts/a_faruma.ttf`. It is not committed to the repository. Copy it from your local font files or the production server after deploying.

---

## Running Tests

```bash
php artisan test
# or
composer test
```

Tests use an in-memory SQLite database (configured in `phpunit.xml`) — no production database is touched.

**Test coverage:**

| Suite | What is tested |
|---|---|
| `Unit\PrayerTimeHelperTest` | `minutesToTime`, `parseDate`, `dayOfYear` — including midnight rollover, negative offsets, date overflows, leap years |
| `Unit\PrayerTimeResolverTest` | Core prayer time calculation with positive/negative offsets, missing data, midnight rollover, `prayersOnly()` |
| `Unit\NearestIslandFinderTest` | Haversine nearest-island logic, inactive exclusion |
| `Feature\WebRoutesTest` | Web routes: load, island/date selection, invalid date fallback, inactive island exclusion, empty state |
| `Feature\Api\IslandsApiTest` | `/api/prayer-times/islands` — response shape, active-only filter, grouping |
| `Feature\Api\NearestApiTest` | `/api/prayer-times/nearest` — valid coords, validation, 404 when no islands |
| `Feature\Api\PrayerTimesApiTest` | `/api/prayer-times` — shape, offset, validation, 404, fallback date, leap year (day 366) |

---

## API Reference

All API routes are rate-limited to **60 requests per minute** per IP.  
All error responses use a consistent JSON envelope: `{"error": "message", "errors": {...}}`.

### `GET /api/prayer-times/islands`

Returns all active islands.

**Response:**
```json
{
  "islands": [
    {
      "id": 1,
      "atoll": "ކ",
      "atoll_latin": "Kaafu",
      "name": "މާލެ",
      "name_latin": "Male",
      "latitude": 4.175,
      "longitude": 73.509,
      "offset_minutes": 0
    }
  ],
  "grouped": {
    "ކ": [ ... ]
  }
}
```

---

### `GET /api/prayer-times/nearest?lat={lat}&lng={lng}`

Find the nearest active island to the given coordinates.

**Parameters:**

| Name | Required | Constraints |
|---|---|---|
| `lat` | yes | numeric, -90 to 90 |
| `lng` | yes | numeric, -180 to 180 |

**Response `200`:**
```json
{
  "island": { "id": 1, "name": "މާލެ", ... }
}
```

**Response `404`:** No island found.  
**Response `422`:** Validation failed.

---

### `GET /api/prayer-times?island_id={id}&date={YYYY-MM-DD}`

Prayer times for an island on a given date.

**Parameters:**

| Name | Required | Notes |
|---|---|---|
| `island_id` | yes | Must be an active island id |
| `date` | no | Defaults to today. Invalid/overflowing dates fall back to today. |

**Response `200`:**
```json
{
  "island": { "id": 1, "name": "މާލެ", "offset_minutes": 0, ... },
  "date": "2026-03-17",
  "prayers": {
    "fajr":    "04:50",
    "sunrise": "06:10",
    "dhuhr":   "12:10",
    "asr":     "15:40",
    "maghrib": "18:15",
    "isha":    "19:10"
  }
}
```

**Response `404`:** No prayer data found for that date.  
**Response `422`:** Missing or invalid `island_id`.

> **Note:** `prayers_raw` (raw minute values) is intentionally excluded from the public API response.

---

## Embeddable Widget

Drop into any webpage:

```html
<!-- Container -->
<div data-salat-widget
     data-island-id="1"
     data-api-base="https://salath.bakeandgrill.mv"
     data-theme="light"
     data-lang="dv">
</div>

<!-- Script (load once, initialises all containers on the page) -->
<script src="https://salath.bakeandgrill.mv/widget.js" defer></script>
```

**Attributes:**

| Attribute | Default | Values |
|---|---|---|
| `data-island-id` | auto (Malé) | integer island id |
| `data-api-base` | same origin as script | full URL without trailing slash |
| `data-theme` | `dark` | `dark` \| `light` |
| `data-lang` | `dv` | `dv` (Dhivehi) \| `en` (English) |

**Multiple widgets** on the same page are supported — each gets a unique DOM id automatically.

**Framework compatibility:** Works in plain HTML, React, Vue, Angular, Blade — any environment that loads vanilla JS.

---

## Caching

| What | Cache key | TTL |
|---|---|---|
| Island list | `prayer_islands_all` | 1 hour |
| Prayer times per island+date | `prayer_times.{id}.{YYYY-MM-DD}` | 24 hours |

After importing fresh data, bust the cache:

```bash
php artisan prayer:clear-cache
# or for a full cache flush:
php artisan cache:clear
```

---

## Deployment Checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_URL` set to the correct HTTPS subdomain
- [ ] Database credentials set in `.env`
- [ ] `PRAYER_TIMES_DB` points to the `salat.db` file (not committed — copy manually)
- [ ] `php artisan migrate --force`
- [ ] `php artisan prayer:import`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `chmod -R 775 storage bootstrap/cache`
- [ ] `public/fonts/a_faruma.ttf` present
- [ ] Webserver document root points to `/public` (not the project root)
- [ ] HTTPS / SSL certificate active
- [ ] Cron/scheduler configured if needed

### Updating from Git

```bash
cd ~/salath.bakeandgrill.mv
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:clear
```

---

## Data Source

Prayer times are stored in a SQLite file (`salat.db`) from the original data source. The application imports this data into the MySQL `prayer_times` table via `php artisan prayer:import`. The source file is **not committed** to this repository — it must be placed at the path defined in `PRAYER_TIMES_DB`.

---

## License

MIT
