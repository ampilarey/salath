# Salat – Maldives Prayer Times

Standalone Laravel app providing prayer times for all islands of Maldives.

## What's included

| Piece | Location |
|-------|----------|
| Full Blade page (island selector, date picker, live countdown) | `resources/views/prayer-times.blade.php` |
| REST API (3 endpoints) | `routes/api.php` |
| Vanilla JS drop-in widget | `public/widget.js` |
| React component (monorepo package) | `../Bake&Grill/packages/prayer-times-widget/` |

## Setup

```bash
cp .env.example .env
php artisan key:generate

# Set the path to your salat.db in .env:
# PRAYER_TIMES_DB=/path/to/salat.db

# For MySQL, update DB_* vars in .env then:
php artisan migrate
php artisan db:seed

# Dev server
php artisan serve --port=8001
```

## API

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/prayer-times/islands` | All active islands (grouped by atoll) |
| GET | `/api/prayer-times/nearest?lat=&lng=` | Nearest island by coordinates |
| GET | `/api/prayer-times?island_id=&date=` | Prayer times for island + date |

## Vanilla JS widget

Embed on any website (Blade, plain HTML):

```html
<div data-salat-widget
     data-api-base="https://salat.yourdomain.mv"
     data-theme="dark"
     data-lang="dv">
</div>
<script src="https://salat.yourdomain.mv/widget.js"></script>
```

`data-theme`: `"dark"` (default) or `"light"`  
`data-lang`: `"dv"` (Dhivehi, default) or `"en"` (English)

## React widget

```tsx
import { PrayerTimesWidget } from '@bakeandgrill/prayer-times-widget';

<PrayerTimesWidget
  apiBase="https://salat.yourdomain.mv"
  theme="dark"
  lang="dv"
/>
```
