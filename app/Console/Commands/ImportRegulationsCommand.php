<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Regulation;
use App\Models\RegulationContent;

class ImportRegulationsCommand extends Command
{
    protected $signature = 'lawlex:import-regulations {--limit=50} {--types=UU,PP,Perpres} {--year=} {--force}';
    protected $description = 'Import Indonesian regulations from Pasal.id MCP API';

    protected $baseUrl = 'https://mcp.pasal.id/mcp';
    protected $token;

    public function handle()
    {
        $this->token = env('PASAL_MCP_TOKEN');
        if (!$this->token) {
            $this->error('PASAL_MCP_TOKEN not set in .env');
            return 1;
        }

        $limit = (int) $this->option('limit');
        $types = explode(',', $this->option('types'));
        $year = $this->option('year');
        $force = $this->option('force');

        $this->info("Importing up to {$limit} regulations...");

        // Search for regulations
        $regulations = $this->searchRegulations($limit, $types, $year);
        
        if (empty($regulations)) {
            $this->error('No regulations found from API');
            return 1;
        }

        $imported = 0;
        $skipped = 0;

        foreach ($regulations as $reg) {
            $existing = Regulation::where('number', $reg['number'] ?? '')
                ->where('year', $reg['year'] ?? 0)
                ->where('type', $reg['type'] ?? '')
                ->first();

            if ($existing && !$force) {
                $skipped++;
                continue;
            }

            // Create or update regulation
            $regulation = Regulation::updateOrCreate(
                [
                    'number' => $reg['number'] ?? '',
                    'year' => $reg['year'] ?? 0,
                    'type' => $reg['type'] ?? '',
                ],
                [
                    'title' => $reg['title'] ?? 'Untitled',
                    'status' => $reg['status'] ?? 'berlaku',
                    'issuing_body' => $reg['issuing_body'] ?? '',
                    'summary' => $reg['summary'] ?? '',
                    'source_url' => $reg['source_url'] ?? '',
                    'published_at' => $reg['published_at'] ?? now(),
                ]
            );

            // Import content/pasal if available
            $this->importRegulationContent($regulation, $reg['law_id'] ?? null);

            $imported++;
            $this->info("Imported: {$reg['type']} {$reg['number']}/{$reg['year']} - {$reg['title']}");
        }

        $this->info("Import complete: {$imported} imported, {$skipped} skipped");
        return 0;
    }

    protected function searchRegulations($limit, $types, $year = null)
    {
        $params = [
            'limit' => min($limit, 20),
            'regulation_types' => $types,
        ];

        if ($year) {
            $params['year'] = (int) $year;
        }

        try {
            $response = Http::withToken($this->token)
                ->post("{$this->baseUrl}/search_legal", $params);

            if (!$response->successful()) {
                $this->error("API Error: {$response->status()} - {$response->body()}");
                return [];
            }

            $data = $response->json();
            return $data['regulations'] ?? [];
        } catch (\Exception $e) {
            $this->error("API Request failed: {$e->getMessage()}");
            return [];
        }
    }

    protected function importRegulationContent($regulation, $lawId)
    {
        if (!$lawId) {
            return;
        }

        try {
            // Get law context
            $context = Http::withToken($this->token)
                ->post("{$this->baseUrl}/get_law_context", [
                    'law' => $lawId,
                    'detail' => 'outline'
                ])->json();

            if (empty($context['outline'])) {
                return;
            }

            foreach ($context['outline'] as $section) {
                RegulationContent::create([
                    'regulation_id' => $regulation->id,
                    'section_type' => $section['type'] ?? 'part',
                    'section_number' => $section['number'] ?? '',
                    'title' => $section['title'] ?? '',
                    'content' => $section['text'] ?? '',
                    'order' => $section['order'] ?? 0,
                ]);
            }
        } catch (\Exception $e) {
            $this->warn("Failed to import content for regulation {$regulation->id}: {$e->getMessage()}");
        }
    }
}
