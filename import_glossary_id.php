<?php
// Re-import kamus_hukum_id.csv (Bahasa Indonesia only) -> legal_glossaries
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$file = '/tmp/kamus_hukum_id.csv';
if (!is_file($file)) { fwrite(STDERR, "CSV not found\n"); exit(1); }

$fh = fopen($file, 'r');
$header = fgetcsv($fh); // No, Istilah, Definisi

// Wipe previous (wrong) import
DB::table('legal_glossaries')->delete();
echo "Cleared old data.\n";

$batch = [];
$seen = [];
$total = 0;
$inserted = 0;
$chunk = 500;
$now = date('Y-m-d H:i:s');

$flush = function (&$batch, &$inserted) {
    if (count($batch) === 0) return;
    DB::table('legal_glossaries')->insert($batch);
    $inserted += count($batch);
    $batch = [];
};

while (($row = fgetcsv($fh)) !== false) {
    if (count($row) < 3) continue;
    $term = trim((string)($row[1] ?? ''));
    $def  = trim((string)($row[2] ?? ''));
    if ($term === '' || $def === '') continue;

    $key = mb_strtolower($term);
    if (isset($seen[$key])) continue;
    $seen[$key] = true;

    // definisi_singkat = first ~180 chars
    $short = mb_substr($def, 0, 180);
    if (mb_strlen($def) > 180) {
        $cut = mb_strrpos($short, ' ');
        if ($cut !== false) $short = mb_substr($short, 0, $cut);
        $short .= '…';
    }

    // Ekstrak singkatan jika term mengandung "/" (mis: "advokat/pengacara/attorney")
    $singkatan = null;
    $termClean = $term;
    if (strpos($term, '/') !== false) {
        $parts = array_map('trim', explode('/', $term));
        $termClean = $parts[0];
        $singkatan = implode(', ', array_slice($parts, 1));
    }

    // Truncate ke batas kolom (term varchar(255), singkatan varchar(100))
    $termClean = mb_substr($termClean, 0, 255);
    if ($singkatan !== null) {
        $singkatan = mb_substr($singkatan, 0, 100);
    }

    $batch[] = [
        'tenant_id' => 'shared',
        'term' => $termClean,
        'singkatan' => $singkatan,
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

echo "Parsed: $total | Inserted: $inserted\n";
