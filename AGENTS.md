# AGENTS.md

## Cursor Cloud specific instructions

This is **Salath — Maldives Prayer Times**, a Laravel 12 (PHP) app with a Vite/Tailwind
frontend. See `README.md` for full architecture, API reference, and standard commands.

### Runtime / gotchas

- **PHP 8.4 is required**, even though `composer.json` says `php: ^8.2`. The committed
  `composer.lock` pins `symfony/*` 8.0 packages that require `php >=8.4`, so `composer install`
  fails on PHP 8.3. Do not run `composer update` to work around this — it would rewrite the lockfile.
- **Local dev uses SQLite** (MySQL is production-only). The environment's `.env` is already set to
  `DB_CONNECTION=sqlite` with the DB file at `database/database.sqlite`.
- **Prayer data source** is the committed SQLite file at `backup databse/salat.db` (the folder name
  contains a space and is intentional). `.env` sets `PRAYER_TIMES_DB="backup databse/salat.db"`
  — the value **must stay quoted** in `.env` or dotenv parsing fails on the space.
- `.env` is git-ignored, so it lives only in the VM snapshot, not in the repo.

### Running the app (dev mode)

- Backend: `php artisan serve --host=0.0.0.0 --port=8000` → app at `/prayer-times`.
- Frontend HMR: `npm run dev` (Vite on port 5173). Built assets are also produced by `npm run build`.
- `composer dev` runs server + queue + logs + vite together via `concurrently`.

### Data / migrations

- Migrate: `php artisan migrate`. Import prayer data: `php artisan prayer:import`
  (reads `PRAYER_TIMES_DB`, seeds ~205 islands and 15,372 prayer-time rows, then bumps the prayer
  cache version). The snapshot already has migrations run and data imported.

### Tests & lint (known pre-existing state)

- Tests: `php artisan test` (or `composer test`). Uses in-memory SQLite (see `phpunit.xml`).
  **3 tests in `tests/Feature/WebRoutesTest.php` fail on a clean checkout** (invalid/overflowing
  date cases expect a 200 fallback but the web request validation returns a 302 redirect). This is
  a pre-existing app/test discrepancy, not an environment issue — 55/58 tests pass.
- Lint: `vendor/bin/pint` (Laravel Pint). `vendor/bin/pint --test` reports **pre-existing** style
  deviations across the committed code; running `pint` without `--test` would reformat many files.

### Optional / non-blocking

- The Dhivehi font `public/fonts/a_faruma.ttf` is not committed. Missing it only affects Thaana glyph
  rendering; all functionality (prayer times, API, island switching) works without it.
