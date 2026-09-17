# Fitur SaaS & Keamanan

Ringkasan gate akun (SaaS layer) yang dipasang di `routes/web.php`.
Urutan middleware pada area inti:
`auth → guest/verified → twofactor → company.active → trial → quota`.

## 1. Verifikasi email

- `User` implements `MustVerifyEmail`; `email_verified_at` backfill untuk
  user lama saat migrasi.
- User yang belum verifikasi **tidak bisa** memakai area inti (AI, workspaces,
  regulations CRUD, dst) — diarahkan ke `/email/verify`.
- Halaman publik/akun (billing, support, dashboard, security, password, 2FA)
  tetap terbuka untuk user terverifikasi mau pun belum.
- Verifikasi via link yang ditandatangani (`signed`) + tombol kirim ulang
  (throttle `3,10`).
- Notifikasi: `App\Notifications\VerifyEmailNotification` (queued).

## 2. 2FA — Email OTP (6 digit)

- Aktif/nonaktif di `/security` (`SecurityController@enableTwoFactor`).
- Alur: active → semua area inti diwajibkan OTP sampai session `2fa_passed`.
  Halaman `/2fa` + `/2fa/resend` diizinkan tanpa OTP.
- Kode disimpan di cache dengan format:
  `['code' => Hash::make(otp), 'expires_at' => <epoch detik>]`,
  key `2fa:{userId}`, berlaku 10 menit.
- **Penting**: `expires_at` harus integer epoch, BUKAN objek `Carbon` —
  cache store `database`/`file` gagal unserialize objek tersebut.
- Throttle: verify `10,1`; resend `3,10`.

## 3. Status perusahaan (tenant gating)

- Middleware `company.active`:
  - Super admin (`role = '1'`) & guest selalu lolos.
  - Status `suspended|inactive|rejected` → redirect `/billing` + flash error.
  - Prefix yang tetap boleh diakses saat non-aktif:
    `/billing`, `/support`, `/email/`, `/2fa`, `/onboarding`,
    `/password-change`, `/security`, `/logout`, `/status`.

## 4. Kuota per paket

- `CheckQuota` (alias `quota:tool`) — tool: `qna`, `draft`, `contract_review`,
  `validity`, `regulations`, `users`.
- 3 dimensi limit dari `plans`:
  - `max_ai_queries` (budget AI bersama) → dibandingkan dgn sum `quota_*`
    perusahaan pada `companies`.
  - `max_regulations` → `company->regulations()->count()`.
  - `max_users` → `company->users()->count()`.
- Tanpa plan aktif → redirect `/billing`. Kuota habis → redirect `/billing`
  (atau JSON 429 untuk XHR).
- Reset bulanan otomatis lewat `quota_reset_at`.
- Increment setelah pemakaian sukses: `CheckQuota::incrementQuota()`.

## 5. Lainnya

- **Onboarding checklist**: `/dashboard` menghitung langkah setups tenant
  (verifikasi email, profil, pilih paket, dll) — `OnboardingController`.
- **Tenant branding via slug**: halaman publik perusahaan di
  `/{company-slug}` (`CompanyPublicController`) untuk profil + paket.
- **Super Admin Analytics**: `/super-admin/analytics` — ringkasan tenant,
  MRR, pemakaian kuota (`AnalyticsController`).
- **Status page**: `/status` publik (`state.blade.php`).
- **Monitoring**: `php artisan monitor:lexlaw`.

## 6. Catatan

- Payment gateway (billing2 / QRIS) sengaja TIDAK diubah/diganti — posisi
  eksisting, menunggu keputusan penyedia (Doku/Midtrans).
- Semua perubahan di layer ini dicakup oleh `tests/Feature/SaaSAuthGateTest.php`.