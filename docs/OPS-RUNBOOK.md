# OPS Runbook

## Cek kesehatan cepat

| Endpoint / Perintah | Harapan |
| --- | --- |
| `GET /up` | 200 (Laravel health) |
| `GET /status` | 200 — halaman status publik (uptime, versi, info DB) |
| `GET /login` | 200 |
| `php artisan monitor:lexlaw` | laporan ringkas DB + disk + status worker |

```bash
ssh lekad
curl -s -o /dev/null -w "%{http_code}\n" https://lexlaw.arktech.id/up
cd ~/project/LAWLEX_v2 && /usr/bin/php8.4 artisan monitor:lexlaw
```

## Log

- Laravel: `~/project/LAWLEX_v2/storage/logs/laravel.log`
  - Bedah 500 terbaru: `grep -n "production.ERROR" storage/logs/laravel.log | tail`
- Nginx: `/var/log/nginx/*.log`
- PHP-FPM: `php8.4-fpm.log` (per konfigurasi server)

## Queue (email async — kritis)

Email (verifikasi, 2FA, notifikasi) diantrekan ke table `jobs`
(`QUEUE_CONNECTION=database`).

- Worker: `php artisan queue:work database --sleep=3 --tries=3 --timeout=90`
  (disarankan `--daemon` via systemd/supervisor).
- Saat ini berjalan manual bonus + watchdog cron:
  `/home/lekadmhd/queues/lawlex-queue-watchdog.sh` setiap menit —
  kalau worker mati, skrip membangunkannya ulang.
- Drain manual: `php artisan queue:work database --once --stop-when-empty`
- Pantau antrean: `SELECT count(*) FROM jobs;` di DB `lawlex`.

## Scheduler

- Cron (crontab lekadmhd) setiap menit:
  `cd ~/project/LAWLEX_v2 && /usr/bin/php8.4 artisan schedule:run`
- Task terjadwal: `artisan monitor:lexlaw:run` (tiap 5 menit — watchdog tambahan).
  Definisi di `routes/console.php`.

## Troubleshooting umum

- **500 di banner trial**: `trial_ends_at` null saat status `trialing` →
  sudah di-guard di `components/layouts/base.blade.php`. Pastikan deploy
  membawa file tsb.
- **2FA 500 "incomplete object"**: cache menyimpan objek `Carbon` — sudah
  diubah ke epoch integer (`expires_at`). JANGAN simpan objek `Carbon`
  langsung ke cache store `database`/`file`.
- **Email tidak terkirim**: cek worker hidup, `jobs` kosong, lalu
  `tail storage/logs/laravel.log | grep -i "smtp\|mail"`.
- **Suspended company tetap masuk**: pastikan alias middleware
  `company.active` terdaftar di `bootstrap/app.php` dan route memakainya.

## Backup & restore

Dokumen lengkap: `docs/BACKUP-RESTORE.md`.

- Otomatis harian ke Google Drive terenkripsi:
  `~/bin/rclone sync ... gdrive-crypt:lexlaw DB Backup`
- Passphrase & key: `/home/lekadmhd/.config/rclone/GDRIVE_CRYPT_KEY.txt`
  dan `~/backups/lawlex/README_restore.md` (di server).

## Post-deploy checklist

1. `curl /up` → 200
2. Login super admin → `/super-admin/analytics` 200
3. Login tenant → `/dashboard` 200
4. `/status` 200
5. `php artisan queue:restart` + pastikan worker hidup
6. Pantau `laravel.log` tak ada `production.ERROR` baru