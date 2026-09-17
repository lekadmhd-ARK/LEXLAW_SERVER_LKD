# Deployment

## Server

- Host: `lekad` (SSH alias, user `lekadmhd`, key `~/Downloads/lekad_server.pem`)
- Stack: Nginx + PHP 8.4-FPM + PostgreSQL, domain `https://lexlaw.arktech.id`
- PHP CLI: `/usr/bin/php8.4`

## Struktur proyek

- App: `~/project/LAWLEX_v2` (owner `lekadmhd`, Group `www-data`)
- Build sementara: `/tmp/lawlex-build` (upload via `scp`), lalu disalin ke proyek
- Cache/config: `bootstrap/cache/` harus writable oleh `www-data`

## Prosedur deploy manual

```bash
# 1. Upload hasil build ke server
scp -r build/* lekad:/tmp/lawlex-build/

# 2. Di server — salin ke proyek (contoh seluruh isi)
cd ~/project/LAWLEX_v2
rsync -av --delete --exclude='.env' --exclude='storage/' /tmp/lawlex-build/ ./

# 3. Dependency (bila composer.json berubah)
composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader

# 4. Migrasi + cache + izin
/usr/bin/php8.4 artisan migrate --force
/usr/bin/php8.4 artisan config:cache
/usr/bin/php8.4 artisan view:cache      # aman sejak komponen x-topbar tersedia
/usr/bin/php8.4 artisan route:list >/dev/null   # SANITASI: JANGAN route:cache (closure routes)
sudo chown -R www-data:www-data bootstrap/cache storage
sudo systemctl reload php8.4-fpm        # atau restart nginx bila perlu

# 5. Queue worker (bila belum jalan / setelah deploy)
/usr/bin/php8.4 artisan queue:restart
```

Catatan:
- **Jangan jalankan `route:cache`** — ada closure route (`Route::...` langsung).
  Verifikasi dengan `route:list` cukup.
- Setelah deploy, baca [OPS-RUNBOOK.md](OPS-RUNBOOK.md) untuk verifikasi cepat.

## Env penting (.env)

- `APP_ENV=production`, `DB_CONNECTION=pgsql`, `DB_DATABASE=lawlex`
- `CACHE_STORE=database`, `QUEUE_CONNECTION=database`
- `MAIL_*` = SMTP Gmail (mindtech.ark@gmail.com)
- Jangan commit `.env`. Template: `.env.example`

## CI/CD

Workflow di `.github/workflows/ci.yml` (GitHub Actions) menjalankan semua unit
test di SQLite `:memory:` pada tiap push/PR. Lihat `docs/CI.md`.