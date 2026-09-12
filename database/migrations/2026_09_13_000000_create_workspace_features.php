<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('team_workspaces')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_mime')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('category')->nullable()->comment('perjanjian,gugatan,putusan,surat_kuasa,lainnya');
            $table->timestamps();
        });

        Schema::create('workspace_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('team_workspaces')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('workspace_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('team_workspaces')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('todo')->comment('todo,in_progress,done,cancelled');
            $table->string('priority')->default('normal')->comment('low,normal,high,urgent');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('workspace_time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('team_workspaces')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('workspace_tasks')->nullOnDelete();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('minutes')->default(0);
            $table->boolean('billable')->default(true);
            $table->date('entry_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_time_entries');
        Schema::dropIfExists('workspace_tasks');
        Schema::dropIfExists('workspace_notes');
        Schema::dropIfExists('workspace_documents');
    }
};
