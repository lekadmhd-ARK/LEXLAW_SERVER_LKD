<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ref = new ReflectionClass(App\Http\Controllers\BillingController::class);
$m = $ref->getMethod('qrisImageForAmount');
$m->setAccessible(true);
$c = new App\Http\Controllers\BillingController();

echo "Rp 99.000  -> " . $m->invokeArgs($c, [99000])  . "\n";
echo "Rp 599.000 -> " . $m->invokeArgs($c, [599000]) . "\n";
echo "Rp 999.000 -> " . $m->invokeArgs($c, [999000]) . "\n";
echo "Rp 100.000 (tanpa file) -> " . $m->invokeArgs($c, [100000]) . "\n";
echo "Rp 10.000 (tanpa file)  -> " . $m->invokeArgs($c, [10000])  . "\n";

// Verifikasi file yang dirujuk memang exists & URL resolve
foreach ([99000, 599000, 999000] as $n) {
    $img = $m->invokeArgs($c, [$n]);
    $path = public_path(ltrim($img, '/'));
    printf("image %s -> exists=%s size=%d B\n", $img, file_exists($path) ? 'Y' : 'N', filesize($path));
}