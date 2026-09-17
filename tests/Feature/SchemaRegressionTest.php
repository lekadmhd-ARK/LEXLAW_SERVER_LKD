<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Regression: memastikan migrate:fresh menghasilkan skema lengkap.
 * Menangkap class "migration marked Ran tapi kolom hilang" yang terjadi
 * di produksi (quota columns 2026-09-17).
 *
 * Berjalan di SQLite (phpunit.xml) — tidak menguji SQL PostgreSQL spesifik.
 */
class SchemaRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // migrate:fresh TIDAK bisa berjalan di dalam transaksi DB (RefreshDatabase),
        // dan setiap test method memakai DB in-memory baru → migrasi fresh di sini.
        Artisan::call('migrate:fresh', ['--force' => true]);
    }

    // ── Companies ───────────────────────────────────────────────

    public function test_companies_has_quota_columns(): void
    {
        $columns = Schema::getColumnListing('companies');

        foreach (['quota_qna', 'quota_draft', 'quota_contract_review', 'quota_validity', 'quota_reset_at'] as $col) {
            $this->assertContains($col, $columns, "companies table missing column: $col");
        }
    }

    // ── Plans ───────────────────────────────────────────────────

    public function test_plans_has_limit_columns(): void
    {
        $columns = Schema::getColumnListing('plans');

        foreach (['limit_qna', 'limit_draft', 'limit_contract_review', 'limit_validity'] as $col) {
            $this->assertContains($col, $columns, "plans table missing column: $col");
        }
    }

    // ── Auth Activities ─────────────────────────────────────────

    public function test_auth_activities_has_device_and_geo_columns(): void
    {
        $columns = Schema::getColumnListing('auth_activities');

        $required = [
            'user_id', 'event', 'email', 'ip_address',
            'mac_address', 'local_ip', 'user_agent', 'device_fingerprint',
            'geo_country', 'geo_region', 'geo_city',
            'geo_lat', 'geo_lon', 'geo_isp',
        ];

        foreach ($required as $col) {
            $this->assertContains($col, $columns, "auth_activities table missing column: $col");
        }
    }

    // ── Queue tables ────────────────────────────────────────────

    public function test_jobs_and_failed_jobs_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('jobs'));
        $this->assertTrue(Schema::hasTable('failed_jobs'));
    }

    // ── Idempotent re-migrate ──────────────────────────────────

    public function test_migrate_fresh_runs_twice_without_error(): void
    {
        Artisan::call('migrate:fresh');
        $exit = Artisan::call('migrate:fresh');
        $this->assertEquals(0, $exit, 'migrate:fresh should exit cleanly on second run');
    }
}
