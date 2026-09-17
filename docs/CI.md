# CI

Workflow GitHub Actions (`.github/workflows/ci.yml`) menjalankan suite test
pada tiap push/PR ke `main`.

## Bagaimana

- Environment: `ubuntu-latest`, PHP 8.4, SQLite `:memory:`.
- `phpunit.xml` menetapkan `APP_ENV=testing`, cache=array, session=array,
  queue=sync, mail=array → **tidak menyentuh data/produksi**.
- Migrasi driver-aware: fitur PostgreSQL (`pg_trgm`, `gin_trgm_ops`,
  `DELETE ... USING`) hanya aktif saat `DB_CONNECTION=pgsql`, jadi
  `migrate:fresh` aman di SQLite untuk CI.

## Mengapa MexlawE2eTest skip di CI

`tests/Feature/LexlawE2eTest.php` dirancang berjalan terhadap **data
produksi** (non-destruktif, tanpa `RefreshDatabase`). Bila seed tidak ada
(`admin@lexlaw.id` atau plans), seluruh suite di-`markTestSkipped`.
Di CI SQLite pasti absent → skip, tidak gagal.

Test SaaS berbasis DB baru ada di `tests/Feature/SaaSAuthGateTest.php`
(RefreshDatabase) dan berjalan penuh di CI.

## Menjalankan lokal

```bash
php artisan config:clear         # penting jika config cache dari produksi masih ada
vendor/bin/phpunit               # setara php artisan test
```

## Bahaya

- JANGAN pernah menjalankan suite yang memakai `RefreshDatabase` terhadap
  DB produksi (`DB_DATABASE=lawlex`). RefreshDatabase = `migrate:fresh`.
  Gunakan SQLite `:memory:` atau DB test khusus (`lawlex_test`).