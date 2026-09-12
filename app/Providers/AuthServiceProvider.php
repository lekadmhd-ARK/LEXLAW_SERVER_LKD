<?php

namespace App\Providers;

use App\Models\TeamWorkspace;
use App\Models\WorkspaceDocument;
use App\Models\WorkspaceNote;
use App\Models\WorkspaceTask;
use App\Policies\WorkspacePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        TeamWorkspace::class    => WorkspacePolicy::class,
        WorkspaceDocument::class => WorkspacePolicy::class,
        WorkspaceNote::class    => WorkspacePolicy::class,
        WorkspaceTask::class    => WorkspacePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Workspace gates for blade @can directives
        Gate::define('manage-members', [WorkspacePolicy::class, 'manageMembers']);
        Gate::define('upload-document', [WorkspacePolicy::class, 'uploadDocument']);
        Gate::define('delete-document', [WorkspacePolicy::class, 'deleteDocument']);
        Gate::define('create-note', [WorkspacePolicy::class, 'createNote']);
        Gate::define('edit-note', [WorkspacePolicy::class, 'editNote']);
        Gate::define('create-task', [WorkspacePolicy::class, 'createTask']);
        Gate::define('edit-task', [WorkspacePolicy::class, 'editTask']);
        Gate::define('log-time', [WorkspacePolicy::class, 'logTime']);
        Gate::define('view-all-time', [WorkspacePolicy::class, 'viewAllTime']);
        Gate::define('view-audit', [WorkspacePolicy::class, 'viewAudit']);
        Gate::define('remove-member', [WorkspacePolicy::class, 'removeMember']);
    }
}
