<?php
// Test script for DraftController
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate controller call
$controller = new \App\Http\Controllers\Ai\DraftController();
$request = new \Illuminate\Http\Request();
$request->setMethod('POST');
$request->request->set('document_type', 'nda');
$request->request->set('instructions', 'Buatkan NDA untuk perusahaan teknologi PT ABC');

try {
    $response = $controller->create($request);
    echo "create() returned: " . get_class($response) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Test view rendering
echo "view exists: " . (view()->exists('ai.draft') ? 'YES' : 'NO') . "\n";
echo "download route: " . (route('ai.draft.download') ? 'YES' : 'NO') . "\n";
