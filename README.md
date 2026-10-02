# Sweet Shop POS

Laravel 12 POS/inventory system, RTL Kurdish-first.

## Laragon setup
1. Enable PHP extensions: `mbstring`, `dom`, `pdo_mysql`, `openssl`.
2. Copy `.env.example` to `.env` and set the real database name/credentials.
3. Run `php artisan key:generate`.
4. Run `php artisan migrate --seed`.
5. For production assets run `npm install` then `npm run build`. If the Vite manifest is absent, the UI has a CDN fallback for local setup.
6. Run `php artisan optimize:clear`.

## Default development account
`admin@sweetshop.local` / `admin12345` — change this immediately for real use.

## Backup
Set `MYSQLDUMP_PATH` when `mysqldump` is not on PATH. Backups are compressed and the newest 14 are retained.
