<?php
// Parse regulations.content_text -> regulation_passages
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Regulation;
use App\Models\RegulationPassage;
use Illuminate\Support\Facades\DB;

DB::table('regulation_passages')->delete();
echo "Cleared old passages.\n";

$regs = Regulation::whereNotNull('content_text')->where('content_text', '<>', '')->get();
$totalPassages = 0;

foreach ($regs as $reg) {
    $text = $reg->content_text;
    // Regex fleksibel tangkap "Pasal X" atau "PASAL X"
    preg_match_all('/(?:Pasal|PASAL)\s+(\d+[A-Za-z]?)\s*\n?(.*?)(?=(?:Pasal|PASAL)\s+\d+[A-Za-z]?|$)/s', $text, $m, PREG_SET_ORDER);
    
    if (empty($m)) {
        // Fallback: anggap seluruh text sebagai 1 pasal umum jika tidak ada pola Pasal
        RegulationPassage::create([
            'tenant_id' => $reg->tenant_id ?? 'shared',
            'regulation_id' => $reg->id,
            'passage_type' => 'pasal',
            'passage_number' => '1',
            'passage_title' => 'Ketentuan Umum / Utuh',
            'content' => mb_substr($text, 0, 5000),
            'hierarchy_path' => '1',
        ]);
        $totalPassages++;
        continue;
    }

    foreach ($m as $idx => $match) {
        $num = trim($match[1]);
        $content = trim($match[2]);
        if ($content === '') continue;

        RegulationPassage::create([
            'tenant_id' => $reg->tenant_id ?? 'shared',
            'regulation_id' => $reg->id,
            'passage_type' => 'pasal',
            'passage_number' => $num,
            'passage_title' => null,
            'content' => $content,
            'hierarchy_path' => (string)($idx + 1),
        ]);
        $totalPassages++;
    }
    echo "Reg #{$reg->id} ({$reg->title}): parsed " . count($m) . " pasals.\n";
}

echo "Total passages parsed & saved: $totalPassages\n";
