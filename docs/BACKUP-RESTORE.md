# Backup & Restore

Backup penuh database (PostgreSQL `lawlex`) di-enkripsi dan di-upload ke
Google Drive setiap malam.

- Tujuan: folder `lexlaw DB Backup` pada akun Google `mindtech.ark@gmail.com`
- Remote rclone: `gdrive-crypt:` (remote terenkripsi, "crypt" obscuring)
- Lokal rclone: `/home/lekadmhd/bin/rclone` (di server `lekad`)
- Folder kerja backup: `/home/lekadmhd/backups/lawlex/`

## Kredensial & passphrase

- Passphrase enkripsi disimpan di server:
  `/home/lekadmhd/.config/rclone/GDRIVE_CRYPT_KEY.txt`
- Panduan restore lengkap (milestone): `/home/lekadmhd/backups/lawlex/README_restore.md`
- ⚠️ Passphrase juga sudah diserahkan ke pemilik (chat). Jangan commit ke git.

## Verifikasi backup (cepat)

```bash
ssh lekad

# Lihat isi remote terenkripsi (nama acak karena crypt):
/home/lekadmhd/bin/rclone lsf gdrive-crypt: --max-depth 1

# Isi remote lewat crypt (nama asli):
/home/lekadmhd/bin/rclone lsf gdrive-crypt:lexlaw\ DB\ Backup
```

Data aman jika nama acak muncul di `lsf` remote crypt (obscuring bekerja).

## Restore (ringkasan)

```bash
export PGPASSWORD=<DB_PASSWORD>
# 1. Ambil file terbaru
/home/lekadmhd/bin/rclone copy 'gdrive-crypt:lexlaw DB Backup' /tmp/restore -v
# 2. Create DB target (jangan menimpa DB produksi langsung; pakai nama baru)
createdb -U lekadmhd -h 127.0.0.1 lawlex_restore_test
# 3. Restore
pg_restore -U lekadmhd -h 127.0.0.1 -d lawlex_restore_test --no-owner \
  --no-privileges /tmp/restore/<file>.dump
# 4. Verifikasi jumlah baris > 0
psql -U lekadmhd -h 127.0.0.1 -d lawlex_restore_test -c
  'SELECT COUNT(*) FROM companies;'   # contoh
# 5. (Bila menggantikan produksi) POIN BESAR: matikan app sebentar, drop & recreate
```

## Bila file restore "tidak bisa dibaca"

`pg_restore: error: unsupported version (1.14) in file header` artinya file
bukan hasil `pg_dump` → kemungkinan ter-ENKRIPSI raw (belum lewat crypt).
Jangan langsung di-dekripsi; pastikan memakai remote `gdrive-crypt:` (bukan
remote `gdrive:` biasa) saat menyalin file backup.

## Verifikasi jarak jauh

- Hanya isi **satu** folder `lexlaw DB Backup` di Drive (distrik),
  jangan menaruh dump mentah di tempat lain.
- Amankan akun Google `mindtech.ark@gmail.com` dengan 2FA Google terkait
  konteks ini.