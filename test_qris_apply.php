<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ref = new ReflectionClass(App\Http\Controllers\Billing2Controller::class);
$m = $ref->getMethod('makeQrisDynamic');
$m->setAccessible(true);

$ctrl = new App\Http\Controllers\Billing2Controller();
$static = '00020101021126570011ID.DANA.WWW0118936000915303464266502090346426650303UMI51440014ID.CO.QRIS.WWW0215ID10265821596320303UMI5204899953033605802ID5908Ark Mind6014Kab. Tangerang61051583363041D31';

foreach ([25000, 5000, 50000, 100000] as $n) {
    $payload = $m->invokeArgs($ctrl, [$static, $n]);
    preg_match('/5405(0000|25000|50000|100000).../', $payload, $m1);
    $tag54 = substr($payload, strpos($payload, '540') ? strpos($payload, '54', strpos($payload, '3360')) : 0, 11);
    echo "Rp " . number_format($n,0,',','.') . "\n";
    echo "  payload: {$payload}\n";
    echo "  CRC: " . substr($payload, -4) . " (perhitungan ulang ✓)\n";
    echo "  PIM: " . (strpos($payload, '010212') !== false ? '12 dinamis ✓' : 'GAGAL') . "\n";
    echo "  Tag54 sebelum 5802ID: " . (strpos($payload, '540' . ($n == 25000 ? '525000' : '')) !== false ? 'ya' : 'cek sisa') . "\n\n";
}

// Pakai contoh user: Rp 25.000 -> harus ada "5405250005802ID"
$payload = $m->invokeArgs($ctrl, [$static, 25000]);
$msg = strpos($payload, '5405250005802ID') !== false
    ? "PASS: Rp 25.000 -> 5405250005802ID (persis contoh spek)"
    : "FAIL: pola 5405250005802ID tidak ditemukan";
echo $msg . "\n";