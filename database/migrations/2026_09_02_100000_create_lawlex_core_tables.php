<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price_monthly', 12, 2)->default(0);
            $table->decimal('price_yearly', 12, 2)->default(0);
            $table->integer('max_users')->default(5);
            $table->integer('max_regulations')->default(100);
            $table->integer('max_ai_queries')->default(50);
            $table->boolean('ai_enabled')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('features')->nullable();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('subscription_status')->default('trialing');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscribed_until')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->after('id')->index();
            $table->foreignId('company_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();
            $table->string('role')->default('viewer')->after('company_id');
        });

        Schema::create('regulations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('number')->nullable();
            $table->year('year')->nullable();
            $table->string('category')->nullable();
            $table->string('status')->default('active');
            $table->date('effective_date')->nullable();
            $table->text('description')->nullable();
            $table->string('source_url')->nullable();
            $table->longText('content_text')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('regulation_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regulation_id')->constrained()->cascadeOnDelete();
            $table->string('article_number')->nullable();
            $table->string('article_title')->nullable();
            $table->longText('content')->nullable();
            $table->json('sub_articles')->nullable();
            $table->timestamps();
        });

        Schema::create('legal_glossaries', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->string('term');
            $table->text('definition');
            $table->string('category')->nullable();
            $table->json('cross_references')->nullable();
            $table->timestamps();
        });

        Schema::create('consolidations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->string('title');
            $table->json('regulation_ids')->nullable();
            $table->longText('consolidated_text')->nullable();
            $table->integer('version')->default(1);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('team_workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('team_workspaces');
        Schema::dropIfExists('consolidations');
        Schema::dropIfExists('legal_glossaries');
        Schema::dropIfExists('regulation_contents');
        Schema::dropIfExists('regulations');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['tenant_id', 'company_id', 'role']);
        });
        Schema::dropIfExists('companies');
        Schema::dropIfExists('plans');
    }
};