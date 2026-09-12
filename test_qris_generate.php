<?php
// Generate QRIS dinamis -> PNG + base64 untuk uji scan offline dengan DANA/mBanking.
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ref = new ReflectionClass(App\Http\Controllers\Billing2Controller::class);
$m = $ref->getMethod('makeQrisDynamic');
$m->setAccessible(true);
$ctrl = new App\Http\Controllers\Billing2Controller();
$static = '00020101021126570011ID.DANA.WWW0118936000915303464266502090346426650303UMI51440014ID.CO.QRIS.WWW0215ID10265821596320303UMI5204899953033605802ID5908Ark Mind6014Kab. Tangerang61051583363041D31';

$outDir = __DIR__.'/public/billing2_test';
if (!is_dir($outDir)) mkdir($outDir, 0775, true);

$nominals = isset($argv[1]) ? [(int)$argv[1]] : [25000, 5000, 50000, 100000];

foreach ($nominals as $n) {
    $payload = $m->invokeArgs($ctrl, [$static, $n]);
    $file = "$outDir/qris_dinamis_rp{$n}.png";

    // tulis payload ke temp string file untuk npx qrcode
    $payloadFile = "$outDir/.payload_rp{$n}.txt";
    file_put_contents($payloadFile, $payload);

    $cmd = sprintf(
        'cd %s && npx --yes qrcode -e M -w 512 -o %s "$(cat %s)" 2>/dev/null',
        escapeshellarg(__DIR__),
        escapeshellarg($file),
        escapeshellarg($payloadFile)
    );
    exec($cmd, $out, $code);

    unlink($payloadFile);

    $b64 = file_exists($file)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($file))
        : null;

    echo "=== Rp " . number_format($n, 0, ',', '.') . " ===\n";
    echo "payload : {$payload}\n";
    echo "PNG     : {$file}" . (file_exists($file) ? " (" . round(filesize($file)/1024, 1) . " KB)" : " GAGAL") . "\n";
    if ($b64) {
        file_put_contents("$outDir/qris_dinamis_rp{$n}.b64.txt", $b64);
        echo "base64  : $outDir/qris_dinamis_rp{$n}.b64.txt (" . round(strlen($b64)/1024, 1) . " KB)\n";
    }
    echo "\n";
}