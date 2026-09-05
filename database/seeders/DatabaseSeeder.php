<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    use \Illuminate\Database\Console\Seeds\WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ── Roles & Permissions ─────────────────────────────
        $roles = ['super-admin', 'owner', 'admin', 'editor', 'viewer'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $permissions = [
            'view plans', 'manage plans',
            'view regulations', 'create regulations', 'edit regulations', 'delete regulations',
            'view consolidated', 'create consolidated',
            'manage users', 'manage workspaces',
            'view billing', 'manage billing',
            'use ai',
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Super-admin gets everything
        $saRole = Role::findByName('super-admin');
        $saRole->syncPermissions(Permission::all());

        // ── Plans ──────────────────────────────────────────
        $plans = [
            [
                'name' => 'Free', 'slug' => 'free',
                'price_monthly' => 0, 'price_yearly' => 0,
                'max_users' => 1, 'max_regulations' => 10, 'max_ai_queries' => 5,
                'ai_enabled' => true, 'is_active' => true,
                'features' => ['ai_basic' => true, 'team' => false, 'word_export' => true],
            ],
            [
                'name' => 'Pro', 'slug' => 'pro',
                'price_monthly' => 149000, 'price_yearly' => 1490000,
                'max_users' => 5, 'max_regulations' => 100, 'max_ai_queries' => 100,
                'ai_enabled' => true, 'is_active' => true,
                'features' => ['ai_basic' => true, 'team' => true, 'word_export' => true],
            ],
            [
                'name' => 'Enterprise', 'slug' => 'enterprise',
                'price_monthly' => 499000, 'price_yearly' => 4990000,
                'max_users' => 50, 'max_regulations' => 10000, 'max_ai_queries' => 1000,
                'ai_enabled' => true, 'is_active' => true,
                'features' => ['ai_basic' => true, 'team' => true, 'word_export' => true, 'priority_support' => true],
            ],
        ];
        foreach ($plans as $plan) {
            Plan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }

        // ── Demo Company + Superadmin ──────────────────────
        $company = Company::firstOrCreate(
            ['slug' => 'lawlex-demo'],
            [
                'tenant_id' => 'tenant-lawlex-demo',
                'name' => 'LawLex Demo',
                'plan_id' => Plan::where('slug', 'pro')->first()->id,
                'subscription_status' => 'active',
                'subscribed_until' => now()->addYear(),
            ]
        );

        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@lawlex.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin123!'),
                'tenant_id' => $company->tenant_id,
                'company_id' => $company->id,
                'role' => 1,
            ]
        );
        $superadmin->assignRole('super-admin');

        // Demo owner
        $owner = User::firstOrCreate(
            ['email' => 'owner@lawlex.test'],
            [
                'name' => 'Demo Owner',
                'password' => Hash::make('Owner123!'),
                'tenant_id' => $company->tenant_id,
                'company_id' => $company->id,
                'role' => 0,
            ]
        );
        $owner->assignRole('owner');
        $owner->givePermissionTo(['view regulations', 'create regulations', 'use ai', 'view billing', 'manage billing', 'manage users', 'manage workspaces']);

        $this->command->info('=== Seeder done ===');
        $this->command->info('Superadmin: superadmin@lawlex.test / Admin123!');
        $this->command->info('Owner:      owner@lawlex.test / Owner123!');
    }
}