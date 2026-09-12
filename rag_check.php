<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenantId = 'tenant-lawlex-demo';
$q = 'bekasi';

// Query yang benar tanpa escaping PHP double-quote di dalam DB string
$rows = DB::select('SELECT r.*, ts_rank(to_tsvector(\'simple\', coalesce(r.title,\'\') \' \' || coalesce(r.short_description,\'\') \' \' || coalesce(r.content_text,\'\') \' \' || coalesce(r.number,\'\')), plainto_tsquery(\'simple\', ?)) AS rank FROM regulations r WHERE r.tenant_id = ? AND to_tsvector(\'simple\', coalesce(r.title,\'\') \' \' || coalesce(r.short_description,\'\') \' \' || coalesce(r.content_text,\'\') \' \' || coalesce(r.number,\'\') ) @@ plainto_tsquery(\'simple\', ?) ORDER BY rank DESC', [$q, $tenantId, $q]);

echo "=== RANK OK ===\n";
foreach ($rows as $r) {
    echo $r->title . " | rank=" . $r->rank . "\n";
}
echo "Total: " . count($rows) . "\n";