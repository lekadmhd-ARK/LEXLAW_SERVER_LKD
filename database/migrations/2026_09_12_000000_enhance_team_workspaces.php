<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_workspaces', function (Blueprint $table) {
            $table->string('type')->nullable()->after('name')->comment('case|general|arbitration|litigation|corporate|consultation');
        });

        Schema::create('team_workspace_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('team_workspaces')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member')->comment('owner|admin|member|viewer');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['workspace_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_workspace_members');

        Schema::table('team_workspaces', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
