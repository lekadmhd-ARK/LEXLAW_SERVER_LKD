<?php
// Import kamus-hukum CSV -> legal_glossaries (streaming, dedup by term)
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$file = '/tmp/kamus_hukum.csv';
if (!is_file($file)) { fwrite(STDERR, "CSV not found\n"); exit(1); }

$fh = fopen($file, 'r');
if (!$fh) { fwrite(STDERR, "Cannot open CSV\n"); exit(1); }

// Read header (strip BOM)
$header = fgetcsv($fh);
// header = [Istilah, Definisi]

$batch = [];
$seen = [];
$total = 0;
$inserted = 0;
$skipped = 0;
$chunk = 500;

$flush = function (&$batch, &$inserted) {
    if (count($batch) === 0) return;
    DB::table('legal_glossaries')->insert($batch);
    $inserted += count($batch);
    $batch = [];
};

while (($row = fgetcsv($fh)) !== false) {
    if (count($row) < 2) continue;
    $term = trim((string)($row[0] ?? ''));
    $def  = trim((string)($row[1] ?? ''));
    if ($term === '' || $def === '') { $skipped++; continue; }

    // dedup by lowercase term
    $key = mb_strtolower($term);
    if (isset($seen[$key])) { $skipped++; continue; }
    $seen[$key] = true;

    // definisi_singkat = first ~180 chars, cut at word boundary
    $short = mb_substr($def, 0, 180);
    if (mb_strlen($def) > 180) {
        $cut = mb_strrpos($short, ' ');
        if ($cut !== false) $short = mb_substr($short, 0, $cut);
        $short .= '…';
    }

    $now = date('Y-m-d H:i:s');
    $batch[] = [
        'tenant_id' => 1,
        'term' => $term,
        'definisi_singkat' => $short,
        'penjelasan_lengkap' => $def,
        'created_at' => $now,
        'updated_at' => $now,
    ];
    $total++;
    if (count($batch) >= $chunk) $flush($batch, $inserted);
}
$flush($batch, $inserted);
fclose($fh);

echo "Parsed: $total | Inserted: $inserted | Skipped(dup/empty): $skipped\n";
