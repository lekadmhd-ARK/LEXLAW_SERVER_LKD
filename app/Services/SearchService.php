<?php

namespace App\Services;

use App\Models\TeamWorkspace;
use App\Models\WorkspaceDocument;
use App\Models\WorkspaceNote;
use App\Models\WorkspaceTask;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SearchService
{
    public function searchWorkspace(
        TeamWorkspace $workspace,
        string $query,
        ?string $type = null,
    ): Collection {
        $query = trim($query);
        if ($query === '') {
            return collect();
        }

        $results = collect();

        // Documents
        if ($type === null || $type === 'documents') {
            $docs = WorkspaceDocument::where('workspace_id', $workspace->id)
                ->where(function ($q) use ($query) {
                    $q->whereRaw('title ILIKE ?', ["%{$query}%"])
                      ->orWhereRaw('description ILIKE ?', ["%{$query}%"])
                      ->orWhereRaw('file_name ILIKE ?', ["%{$query}%"]);
                })
                ->limit(10)
                ->get()
                ->map(fn($d) => (object)[
                    'type'        => 'document',
                    'type_label'  => 'Dokumen',
                    'title'       => $d->title,
                    'excerpt'     => \Illuminate\Support\Str::limit($d->description ?? $d->file_name, 100),
                    'url'         => "/team-workspaces/{$workspace->id}?tab=documents",
                    'sub'         => $d->category_name,
                    'date'        => $d->created_at?->format('d M Y'),
                ]);
            $results = $results->merge($docs);
        }

        // Notes
        if ($type === null || $type === 'notes') {
            $notes = WorkspaceNote::where('workspace_id', $workspace->id)
                ->where(function ($q) use ($query) {
                    $q->whereRaw('title ILIKE ?', ["%{$query}%"])
                      ->orWhereRaw('content ILIKE ?', ["%{$query}%"]);
                })
                ->limit(10)
                ->get()
                ->map(fn($n) => (object)[
                    'type'        => 'note',
                    'type_label'  => 'Catatan',
                    'title'       => $n->title,
                    'excerpt'     => \Illuminate\Support\Str::limit(strip_tags($n->content), 100),
                    'url'         => "/team-workspaces/{$workspace->id}?tab=notes",
                    'sub'         => $n->user?->name,
                    'date'        => $n->created_at?->format('d M Y'),
                ]);
            $results = $results->merge($notes);
        }

        // Tasks
        if ($type === null || $type === 'tasks') {
            $tasks = WorkspaceTask::where('workspace_id', $workspace->id)
                ->where(function ($q) use ($query) {
                    $q->whereRaw('title ILIKE ?', ["%{$query}%"])
                      ->orWhereRaw('description ILIKE ?', ["%{$query}%"]);
                })
                ->limit(10)
                ->get()
                ->map(fn($t) => (object)[
                    'type'        => 'task',
                    'type_label'  => 'Tugas',
                    'title'       => $t->title,
                    'excerpt'     => \Illuminate\Support\Str::limit($t->description, 100),
                    'url'         => "/team-workspaces/{$workspace->id}?tab=tasks",
                    'sub'         => $t->priority_name,
                    'date'        => $t->due_date?->format('d M Y') ?? $t->created_at?->format('d M Y'),
                ]);
            $results = $results->merge($tasks);
        }

        return $results->sortByDesc('date');
    }
}
