# LEXLAW v2 — Pusat Informasi Hukum (SaaS)

Sistem informasi hukum multi-tenant untuk konsultan & perusahaan: database
putusan, regulasi live dari BPK/JDIH, glossary, konsolidasi peraturan, dan
asisten AI hukum (Q&A, draft DOCX, contract review, validity check) dengan
manajemen paket langganan, verifikasi email, 2FA, dan kuota per tenant.

**Produksi:** https://lexlaw.arktech.id

![CI](https://github.com/lekadmhd-ARK/LEXLAW_SERVER_LKD/actions/workflows/ci.yml/badge.svg)

## Fitur utama

- **AI Lex** — Lex Q&A (jawaban berbasis sumber resmi, multi-sumber +
  validasi silang), Draft DOCX, Contract Reviewer, Validity Checker.
- **Database hukum** — Putusan (feeds & pencarian), Regulasi tersinkron dari
  `peraturan.bpk.go.id` / JDIH, Glossary hukum, Konsolidasi peraturan (+
  RAG), monitoring otomatis.
- **Multi-tenant / SaaS** — plans & billing (QRIS dinamis), verifikasi email,
  2FA email OTP, gating status perusahaan (suspended/inactive/rejected),
  kuota pemakaian per paket, onboarding checklist, branding publik per slug.
- **Super Admin** — dashboard analytics (tenant, MRR, pemakaian kuota),
  manajemen plans & companies, audit perhatian `companies`→`Analytics`.
- **Notifikasi & teams** — workspaces, anggota, deadline, email async via
  queue.
- **Status publik** — halaman `/status` + health check `/up`.

## Teknologi

Laravel 12 · PHP 8.4 · PostgreSQL · Livewire/Filament (admin) · Tailwind ·
Queue `database` · Backup terenkripsi rclone (Google Drive). Tanpa frontend
build step — Blade + asset statis.

## Dokumentasi

| Dokumen | Isi |
| --- | --- |
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | Deploy manual ke server (nginx + php8.4-fpm + postgres) |
| [docs/OPS-RUNBOOK.md](docs/OPS-RUNBOOK.md) | Cek kesehatan, log, queue worker, scheduler, troubleshooting |
| [docs/SAAS-SECURITY.md](docs/SAAS-SECURITY.md) | Gate akun: verifikasi email, 2FA, status company, kuota |
| [docs/BACKUP-RESTORE.md](docs/BACKUP-RESTORE.md) | Backup harian terenkripsi ke Google Drive & prosedur restore |
| [docs/CI.md](docs/CI.md) | Pipeline pengujian (GitHub Actions) |

## Menjalankan tes

```bash
php artisan config:clear   # hindari config cache produksi menimpa env test
vendor/bin/phpunit         # SQLite :memory:, tanpa menyentuh data produksi
```

- `tests/Feature/SaaSAuthGateTest.php` — test gate SaaS (RefreshDatabase).
- `tests/Feature/LexlawE2eTest.php` — walkthrough non-destruktif terhadap
  **data produksi**; otomatis skip bila seed tidak tersedia (CI).

## Environment

Salin `.env.example` → `.env`. Variabel kunci: `APP_ENV`, `DB_CONNECTION=pgsql`,
`CACHE_STORE=database`, `QUEUE_CONNECTION=database`, `MAIL_*`. Jangan commit
`.env` / berkas secret apa pun.

## Keamanan singkat

- Verify email + 2FA OTP email wajib untuk area inti.
- Company non-aktif diarahkan ke `/billing` (radius terpisah utk billing,
  support, security).
- Kuota AI/regulasi/user dibatasi sesuai paket; super admin bebas kuota.
- Lihat [docs/SAAS-SECURITY.md](docs/SAAS-SECURITY.md) untuk detail.

> ⚠️ Jangan menjalankan suite yang memakai `RefreshDatabase` terhadap DB
> produksi (`DB_DATABASE=lawlex`) — itu = `migrate:fresh`.