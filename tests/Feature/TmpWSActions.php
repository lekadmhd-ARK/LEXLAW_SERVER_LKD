<?php

namespace Tests\Feature;

use App\Models\TeamWorkspace;
use App\Models\WorkspaceDocument;
use App\Models\WorkspaceNote;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Reproduksi alur workspace di DB produksi (user 58 / workspace 11) dalam
 * transaksi yang di-rollback, untuk menangkap pesan error sebenarnya.
 */
class TmpWSActions extends TestCase
{
    use DatabaseTransactions;

    private array $env = [];

    private array $probs = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (file(base_path('.env')) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $this->env[trim($k)] = trim($v, " \t\n\r\"'");
        }

        config([
            'database.default' => 'pgsql',
            'database.connections.pgsql.host' => $this->env['DB_HOST'] ?? '127.0.0.1',
            'database.connections.pgsql.port' => $this->env['DB_PORT'] ?? '5432',
            'database.connections.pgsql.database' => $this->env['DB_DATABASE'],
            'database.connections.pgsql.username' => $this->env['DB_USERNAME'],
            'database.connections.pgsql.password' => $this->env['DB_PASSWORD'] ?? '',
        ]);
        config(['session.driver' => 'file', 'app.debug' => true]);
    }

    private function hit(string $method, string $path): void
    {
        try {
            $r = $this->call($method, $path, $this->payload ?? []);
            $status = $r->status();
            if ($status >= 500 || $status === 419) {
                $msg = '';
                if (preg_match('/<title>(.*?)<\/title>/s', $r->content(), $m)) {
                    $msg = trim(html_entity_decode(strip_tags($m[1])));
                }
                $this->probs[] = "$method /$path => $status :: $msg";
            } else {
                $this->probs[] = "OK $status : $method /$path";
            }
        } catch (\Throwable $e) {
            $cause = $e;
            while ($cause->getPrevious()) {
                $cause = $cause->getPrevious();
            }
            $this->probs[] = "$method /$path => EXC " . $cause::class . ' :: ' . $cause->getMessage();
        }
    }

    public function test_workspace_flows(): void
    {
        $u = User::withoutGlobalScopes()->find(58);
        $ws = TeamWorkspace::withoutGlobalScopes()->find(11);
        $this->assertNotNull($u, 'user 58');
        $this->assertNotNull($ws, 'workspace 11');

        $this->actingAs($u);

        // --- GET semua tab workspace (query string) ---
        foreach (['members', 'documents', 'notes', 'tasks', 'time'] as $tab) {
            $this->hit('GET', "team-workspaces/11?tab=$tab");
        }
        $this->hit('GET', "team-workspaces/11?tab=unknown");

        // --- GET semua tab workspace ---
        foreach (['', '/members', '/documents', '/notes', '/tasks', '/time-entries'] as $tab) {
            $this->hit('GET', "team-workspaces/11$tab");
        }

        // --- throwaway user sesama tenant untuk member flow ---
        $member = User::create([
            'name' => 'SWEEP MEMBER',
            'email' => 'sweep-member-' . Str::random(6) . '@example.com',
            'password' => bcrypt('password123'),
            'tenant_id' => $u->tenant_id,
            'company_id' => $u->company_id,
            'role' => 'member',
        ]);

        // members
        $this->payload = ['email' => $member->email, 'role' => 'member'];
        $this->hit('POST', "team-workspaces/11/members");
        $this->payload = ['role' => 'editor'];
        $this->hit('PATCH', "team-workspaces/11/members/{$member->id}");
        $this->hit('DELETE', "team-workspaces/11/members/{$member->id}");
        unset($this->payload);

        // notes
        $this->payload = ['title' => 'Sweep Note ' . Str::random(4), 'content' => 'Isi catatan pengujian'];
        $this->hit('POST', "team-workspaces/11/notes");
        $note = WorkspaceNote::withoutGlobalScopes()->where('title', 'like', 'Sweep Note %')->latest('id')->first();
        if ($note) {
            $this->payload = ['title' => 'Sweep Note U', 'content' => 'Isi revisi'];
            $this->hit('PATCH', "team-workspaces/11/notes/{$note->id}");
            $this->payload = [];
            $this->hit('DELETE', "team-workspaces/11/notes/{$note->id}");
        }

        // tasks
        $this->payload = ['title' => 'Sweep Task ' . Str::random(4), 'priority' => 'high'];
        $this->hit('POST', "team-workspaces/11/tasks");
        $task = WorkspaceTask::withoutGlobalScopes()->where('title', 'like', 'Sweep Task %')->latest('id')->first();
        if ($task) {
            $this->payload = ['status' => 'done'];
            $this->hit('PATCH', "team-workspaces/11/tasks/{$task->id}");
            $this->payload = [];
            $this->hit('PATCH', "team-workspaces/11/tasks/{$task->id}/toggle");
            $this->hit('DELETE', "team-workspaces/11/tasks/{$task->id}");
        }

        // time entries
        $this->payload = ['hours' => 1, 'minutes' => 30, 'entry_date' => now()->toDateString(), 'billable' => 1];
        $this->hit('POST', "team-workspaces/11/time-entries");
        $entry = WorkspaceTimeEntry::withoutGlobalScopes()->where('description', null)->latest('id')->first();
        if ($entry) {
            $this->payload = [];
            $this->hit('DELETE', "team-workspaces/11/time-entries/{$entry->id}");
        }

        // documents
        $this->payload = ['title' => 'Sweep Doc ' . Str::random(4), 'category' => 'lainnya', 'file' => UploadedFile::fake()->create('sweep.pdf', 5, 'application/pdf')];
        $this->hit('POST', "team-workspaces/11/documents");
        $doc = WorkspaceDocument::withoutGlobalScopes()->where('title', 'like', 'Sweep Doc %')->latest('id')->first();
        if ($doc) {
            Storage::disk('r2')->delete($doc->file_path);
            $this->payload = [];
            $this->hit('DELETE', "team-workspaces/11/documents/{$doc->id}");
        }
        unset($this->payload);

        $report = '===== WS FLOWS (prod DB) =====' . PHP_EOL . implode(PHP_EOL, $this->probs) . PHP_EOL . '===== END =====';
        file_put_contents(storage_path('ws-smoke.txt'), $report);

        $this->assertTrue(true);
    }
}