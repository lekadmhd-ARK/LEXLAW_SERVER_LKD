<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\Regulation;
use App\Models\RegulationPassage;
use Illuminate\Support\Facades\DB;

$regs = Regulation::whereNotNull('content_text')->get();
$total_parsed = 0;

foreach ($regs as $r) {
    $text = $r->content_text ?? '';
    if (trim($text) === '') continue;
    
    // Split by "Pasal X" pattern
    $parts = preg_split('/\bPasal\s+\d+/i', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE);
    
    $passages = [];
    $offset = 0;
    
    foreach ($parts as $idx => $part) {
        $text_part = $part[1];
        $pos = $part[2];
        
        if ($idx === 0) {
            // Section before first "Pasal"
            if (strlen(trim($text_part)) > 0) {
                $passages[] = [
                    'passage_type' => 'pembukaan',
                    'passage_number' => '',
                    'passage_title' => '',
                    'content' => trim($text_part),
                    'regulation_id' => $r->id,
                ];
            }
            $offset = $pos + strlen($text_part);
        }
        
        if ($idx < count($parts) - 1) {
            $next_start = $parts[$idx + 1][2];
            $content = trim(substr($text, $offset, $next_start - $offset));
            if (strlen($content) > 10) {
                $passages[] = [
                    'passage_type' => 'pasal',
                    'passage_number' => '',
                    'passage_title' => '',
                    'content' => $content,
                    'regulation_id' => $r->id,
                ];
            }
            $offset = $next_start;
        }
    }
    
    foreach ($passages as $p) {
        RegulationPassage::updateOrCreate(
            ['regulation_id' => $p['regulation_id'], 'passage_number' => $p['passage_number']],
            [
                'passage_type' => $p['passage_type'],
                'passage_title' => $p['passage_title'],
                'content' => $p['content'],
                'hierarchy_path' => '',
                'parent_id' => null,
            ]
        );
    }
    $total_parsed++;
}

echo "Selesai. Regulasi punya content_text: " . count($regs) . ", Parsed passages: " . $total_parsed . "\n";