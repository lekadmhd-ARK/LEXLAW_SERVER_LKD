# LEXLAW v2 — Pusat Informasi Hukum Multi-Tenant (SaaS)

Sistem perangkat lunak hukum untuk kantor konsultan & perusahaan: basis data
putusan dan regulasi (tersinkron langsung dari BPK/JDIH), glossary, konsolidasi
peraturan, dan **asisten AI hukum** (Lex Q&A, Draft DOCX, Contract Reviewer,
Validity Checker) — dibungkus lapisan SaaS lengkap: paket & billing, verifikasi
email, 2FA email OTP, kuota per tenant, dan dashboard analitik untuk super admin.

**Produksi:** https://lexlaw.arktech.id
**Status CI:** ![CI](https://github.com/lekadmhd-ARK/LEXLAW_SERVER_LKD/actions/workflows/ci.yml/badge.svg)

---

## Daftar isi

- [Fitur utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Struktur proyek](#struktur-proyek)
- [Model data](#model-data)
- [Endpoint utama](#endpoint-utama)
- [Perintah artisan](#perintah-artisan)
- [Scheduler & cron](#scheduler--cron)
- [Konfigurasi environment](#konfigurasi-environment)
- [Setup lokal](#setup-lokal)
- [Pengujian](#pengujian)
- [Deployment & operasional](#deployment--operasional)
- [Keamanan](#keamanan)
- [Troubleshooting](#troubleshooting)
- [Roadmap](#roadmap)

---

## Fitur utama

### Asisten AI hukum (multi-sumber resmi)
- **Lex Q&A** (`/ai/lex-qna`) — jawaban berbasis sumber resmi; menggabungkan
  `peraturan.bpk.go.id`, DuckDuckGo-filter-`go.id`, validasi silang, cache 7 hari.
- **Draft DOCX** (`/ai/draft`) — bikin draf dokumen hukum (surat perjanjian dll.)
  dari instruksi, beberapa varian.
- **Contract Reviewer** (`/ai/contract-review`) — ringkasan & temuan kontrak.
- **Validity Checker** (`/ai/validity`) — cek keabsahan pasal/UU yang dirujuk.

### Basis data hukum
- **Putusan** — feeds putusan (`/decisions`) + database pencarian (`/putusans`),
  kategori & PNS. Impor via command `putusan:fetch`.
- **Regulasi** — sinkron `peraturan.bpk.go.id` / JDIH, detail + PDF,
  full-text search (PostgreSQL GIN / MySQL FULLTEXT), unik per judul
  (case-insensitive).
- **Glossary** — istilah hukum + fuzzy search (`pg_trgm` di PostgreSQL).
- **Konsolidasi** — gabungkan beberapa peraturan jadi satu teks konsolidasi,
  versi, dukungan RAG (chunking + passage).

### Lapisan SaaS / tenant
- **Akun & keamanan** — verifikasi email, **2FA email OTP (6 digit, 10 menit)**,
  gating status perusahaan (`suspended|inactive|rejected` → `/billing`),
  kuota per paket (AI, regulasi, user), reset kuota bulanan.
- **Billing** — paket langganan (Starter/Pro/Enterprise), QRIS dinamis,
  webhook pembayaran, halaman billing per tenant.
- **Onboarding** — checklist setup tenant di `/dashboard`.
- **Branding publik** — halaman profil perusahaan per `/{company-slug}`.
- **Super admin** — `/super-admin/analytics` (meta tenant, MRR, pemakaian kuota),
  manajemen plans & companies (approve/reject/suspend).

### Kolaborasi & admin
- Workspaces tim (dokumen, catatan, tugas, time-entry), notifikasi, audit log,
  laporan (reports), support ticket, halaman publik (status, privacy, DPA,
  terms). Panel admin Filament di `/admin`.

---

## Teknologi

| Lapisan | Pilihan |
| --- | --- |
| Backend | **Laravel 13** (^13.17), PHP 8.4 |
| Database | PostgreSQL (produksi), SQLite `:memory:` (tes/CI) |
| Frontend | Blade + komponen native (tanpa build step; asset statis) |
| Panel admin | Filament/Livewire (`/admin`) |
| Queue & cache | `database` (jobs, cache, sessions) |
| Email | SMTP Gmail (mindtech.ark@gmail.com) — **async via queue** |
| AI | HTTP ke `AI_BASE_URL` + `AI_MODEL` (`AI_API_KEY`) |
| Billing | QRIS dinamis + webhook lokal; integrasi Doku dikonfigurasi (`DOKU_*`) |
| Backup | `pg_dump` → rclone crypt → Google Drive (otomatis harian) |
| Testing | PHPUnit 12, SQLite `:memory:` |

> ⚠️ Payment gateway saat ini memakai implementasi lokal QRIS yang sudah ada
> dan **sengaja tidak diubah**; penyedia (Doku/Midtrans) menunggu verifikasi.

---

## Struktur proyek

```
app/
├─ Console/Commands/        # putusan:fetch, lawlex:import-regulations, lexlaw:monitor
├─ Http/Controllers/
│  ├─ Ai/                   # LexQna, Draft, AdvancedAi, ValidityChecker
│  ├─ Auth/                 # login/register/password/verification
│  ├─ SuperAdmin/           # Analytics, Company, Plan
│  └─ ...                   # Billing*, Regulation, Putusan, Glossary, Workspace*, dst.
├─ Http/Middleware/         # EnsureCompanyActive, RequireTwoFactor, CheckQuota,
│                           # EnsureSuperAdmin, TrialMiddleware (+ trusted proxies)
├─ Mail/                    # WelcomeMail, PaymentConfirmationMail
├─ Models/                  # Company, Plan, User, Regulation, Putusan, dst.
├─ Notifications/           # VerifyEmail, TwoFactorCode, Task/Doc/Member/Deadline
└─ Services/                # LegalSourceService, SearchService, AuditService
bootstrap/app.php           # alias middleware + exception handling
config/
database/migrations/        # 27 migrasi (driver-aware utk pg & sqlite)
routes/
├─ web.php                  # ±185 route
└─ console.php              # jadwal: lexlaw:monitor tiap 5 menit
resources/views/            # Blade layout base + sidebar + halaman per fitur
tests/
├─ Feature/SaaSAuthGateTest.php   # test gate SaaS (RefreshDatabase)
├─ Feature/LexlawE2eTest.php      # walkthrough non-destruktif vs data produksi
└─ Feature/LegalSourceServiceTest.php
docs/                       # dokumentasi ops (lihat tabel di bawah)
```

---

## Model data

Tabel inti (dari migrasi):

| Kelompok | Tabel |
| --- | --- |
| Identity | `users`, `permissions`/`roles` (Spatie), `sessions`, `cache`, `jobs`/`failed_jobs` |
| Tenant & SaaS | `plans`, `companies`, `company_settings`, `webhooks` |
| Hukum | `regulations`, `regulation_contents`, `regulation_passages`, `legal_glossaries`, `consolidations`, `consolidation_chunks`, `putusans`, `putusan_pns`, `putusan_categories` |
| Workspace | `team_workspaces`, `team_workspace_members`, `workspace_documents|notes|tasks|time_entries` |
| Ops | `audit_logs`, `ai_chat_history` |

Relasi penting: `users.company_id → companies.id`; `companies.plan_id → plans.id`;
setiap company punya `tenant_id` (UUID) yang diwarisi user; regulasi/putusan
bersifat **publik lintas tenant** (tampil untuk semua), sedangkan workspace &
AI bersifat per-tenant.

Lightweight columns tambahan untuk kuota: `plans.max_ai_queries`,
`plans.max_regulations`, `plans.max_users`, `plans.limit_*`;
`companies.quota_qna|draft|contract_review|validity`, `companies.quota_reset_at`.

---

## Endpoint utama

| Area | Route contoh |
| --- | --- |
| Publik | `/`, `/status`, `/regulations`, `/regulations/{id}`, `/regulations/{id}/pdf`, `/{company-slug}` |
| Auth | `/login`, `/register`, `/forgot-password`, `/reset-password`, `/email/verify`, `/2fa` |
| Tenant | `/dashboard`, `/billing`, `/billing2`, `/support`, `/onboarding`, `/security`, `/password-change`, `/branding`, `/reports` |
| Legal | `/decisions`, `/putusans`, `/regulations` (CRUD leburnya via `quota:regulations`), `/legal-glossary`, `/consolidations` |
| AI | `/ai/lex-qna`, `/ai/draft`, `/ai/validity`, `/ai/contract-review` (semua `quota:*`) |
| Workspace | `/team-workspaces` dan sub-resource dokumen/catatan/tugas/time-entry |
| Super admin | `/super-admin/analytics`, `/super-admin/plans`, `/super-admin/companies/{id}/approve\|reject\|suspend` |
| Ops | `/up` (health), `/admin` (Filament) |
| Pembayaran | `/billing2/make-dynamic`, `webhook.payment` |

Template middleware area inti:
`auth → guest/verified → twofactor → company.active → trial → quota`.
Detail lengkap: [docs/SAAS-SECURITY.md](docs/SAAS-SECURITY.md).

---

## Perintah artisan

| Command | Fungsi |
| --- | --- |
| `php artisan lexlaw:monitor` | Cek kesehatan (DB, disk, worker) — report ringkas |
| `php artisan putusan:fetch` | Impor/update data putusan |
| `php artisan lawlex:import-regulations --limit=50 --types=UU,PP,Perpres --year= --force` | Impor regulasi dari sumber resmi |
| `php artisan db:monitor` / `queue:monitor` | Bawaan Laravel, dipakai watchdog |
| `php artisan queue:work database --queue=default` | Worker email/notifikasi async |

---

## Scheduler & cron

- `routes/console.php` menjadwalkan `lexlaw:monitor` tiap 5 menit.
- Cron `lekadmhd` (tiap menit):
  ```
  cd /home/lekadmhd/project/LAWLEX_v2 && /usr/bin/php8.4 artisan schedule:run
  ```
- Watchdog queue terpisah: `/home/lekadmhd/queues/lawlex-queue-watchdog.sh`
  (membangunkan worker jika mati).

---

## Konfigurasi environment

Salin `.env.example` → `.env`. Nama variabel (tanpa nilai/rahasia):

| Kelompok | Variabel |
| --- | --- |
| App | `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`, `APP_LOCALE`, `APP_MAINTENANCE_DRIVER` |
| DB | `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` |
| Session/cache/queue | `SESSION_DRIVER`, `SESSION_SECURE_COOKIE`, `SESSION_SAME_SITE`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`, `REDIS_*` (opsional), `MEMCACHED_HOST` |
| Mail | `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_*` |
| AI | `AI_BASE_URL`, `AI_API_KEY`, `AI_MODEL` |
| Payment | `DOKU_CLIENT_ID`, `DOKU_SECRET_KEY`, `DOKU_API_KEY`, `DOKU_SANDBOX` |
| Resend/eksternal | `RESEND_API_KEY`, `FRONTEND_URL`, `FILESYSTEM_DISK`, `AWS_*` |

> 🔒 Jangan commit `.env`, `.env.backup`, `*.env.prod_bak*`, atau berkas
> berisi credential. Gunakan `.env.example` sebagai template.

---

## Setup lokal

```bash
git clone <repo>
cp .env.example .env
composer install

# SQLite cukup untuk tes; produksi pakai PostgreSQL
# (buat DB lalu set DB_CONNECTION/DB_DATABASE di .env)
php artisan migrate --seed
php artisan serve
php artisan key:generate --show   # isi APP_KEY bila belum ada
```

Akses super admin: user dengan `role = '1'` (mis. `admin@lexlaw.id`); admin
panel Filament di `/admin/login`.

---

## Pengujian

```bash
php artisan config:clear   # penting: jangan biarkan config cache produksi menimpa env test
vendor/bin/phpunit         # ≡ php artisan test
```

- `SaaSAuthGateTest` — verifikasi email, 2FA, status company, kuota (SQLite `:memory:`, RefreshDatabase).
- `LegalSourceServiceTest` — parsing BPK/DDG dengan `Http::fake` (tanpa internet).
- `LexlawE2eTest` — walkthrough **non-destruktif** terhadap data produksi;
  seluruh suite **skip otomatis** bila seed (`admin@lexlaw.id` + plans) tidak ada.
- CI (GitHub Actions): `.github/workflows/ci.yml` — PHP 8.4 + SQLite, tiap push/PR.

> ⚠️ Jangan jalankan suite dengan `RefreshDatabase` terhadap DB produksi
> (`DB_DATABASE=lawlex`) — itu setara `migrate:fresh`.

---

## Deployment & operasional

Server `lekad` = Nginx + PHP 8.4-FPM + PostgreSQL (domain `lexlaw.arktech.id`).

- **Deploy manual:** [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) — proses upload,
  `migrate --force`, `config:cache`, `view:cache`, owner `www-data`, reload FPM.
  **Jangan `route:cache`** (closure routes).
- **Operasi harian & troubleshooting:** [docs/OPS-RUNBOOK.md](docs/OPS-RUNBOOK.md)
  — cek `/up`, `/status`, `lexlaw:monitor`, drain queue, log `laravel.log`.
- **Backup & restore (Google Drive terenkripsi):**
  [docs/BACKUP-RESTORE.md](docs/BACKUP-RESTORE.md).
- **CI:** [docs/CI.md](docs/CI.md).

---

## Keamanan

1. **Verifikasi email** wajib sebelum fitur inti (area billing/support/keamanan tetap terbuka).
2. **2FA OTP email** opsional namun wajib dilalui bila diaktifkan.
3. **Gating status tenant**: `suspended|inactive|rejected` → `/billing`.
4. **Kuota** berbasis paket (`max_ai_queries`, `max_regulations`, `max_users`).
5. **Jangan simpan objek `Carbon` di cache** `database`/`file` — gunakan epoch integer (bug klasik yang sudah diperbaiki di 2FA).
6. Backfill verifikasi email hanya dilakukan sekali saat deploy (`UPDATE users SET email_verified_at=now()`).

---

## Troubleshooting ringkas

| Gejala | Solusi |
| --- | --- |
| Email tak terkirim | Worker hidup? `SELECT count(*) FROM jobs;`, cek `laravel.log` grep smtp |
| 500 di banner trial | `trial_ends_at` null + status `trialing` — pakai deploy terbaru (`base.blade.php` sudah null-safe) |
| 2FA → 500 `incomplete object` | Cache berisi `Carbon` — pastikan `expires_at` integer epoch |
| `view:cache` gagal | Komponen `x-topbar` hilang — tambahkan `resources/views/components/topbar.blade.php` |
| Suspended bisa login | Pastikan alias `company.active` terdaftar & dipakai route |
| Test skenario AI | Pakai `Http::fake` + mock `LegalSourceService` (lihat `LexlawE2eTest`) |

---

## Roadmap

- [ ] Penggantian payment gateway lokal QRIS dengan Doku/Midtrans (mengikuti instruksi owner).
- [ ] Porting LexlawE2eTest ke DB test (bukan produksi) agar bisa selalu jalan di CI.
- [ ] Ekspansi RAG untuk konsolidasi (chunking passage sudah tersedia).

---

## Lisensi & kontak

Proyek internal © 2026 LEXLAW. Akses repo & kredensial dikelola pemilik
(`lekadmhd-ARK`). Dukungan via halaman `/support` aplikasi.