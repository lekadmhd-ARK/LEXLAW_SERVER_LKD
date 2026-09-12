<?php
// E2E local test untuk AI controllers — tanpa mengubah password user.
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

$pass = 0; $fail = 0;

function report($name, $ok, $detail = '') {
    global $pass, $fail;
    echo ($ok ? "[PASS] " : "[FAIL] ") . $name . ($detail ? " :: $detail" : "") . "\n";
    $ok ? $pass++ : $fail++;
}

// ==== 1. DB DRIVER ====
report('DB driver = pgsql', DB::getDriverName() === 'pgsql', DB::getDriverName());

// ==== 2. Regex query di controller bekerja dengan pgsql ====
try {
    $r = DB::select("SELECT to_tsquery('english', ?) AS q", ['hukum']);
    report('to_tsvector SQL valid', true, 'query -> ' . ($r[0]->q ?? 'null'));
} catch (\Throwable $e) {
    report('to_tsvector SQL valid', false, $e->getMessage());
}

// ==== 3. LexQnaController::chat (langsung, tanpa login) ====
try {
    $req = new Request();
    $req->setMethod('POST');
    $req->request->set('question', 'Apa dasar hukum kontrak kerja di Indonesia?');
    $ctrl = new \App\Http\Controllers\Ai\LexQnaController();
    $resp = $ctrl->chat($req);
    // Ini return view. Kita ambil answer dari session history.
    $history = session('lexqna_history', []);
    $last = end($history);
    report('LexQna chat ran', is_object($resp), get_class($resp) . ' / answer len=' . strlen($last['content'] ?? ''));
    if (!($last['content'] ?? '')) report('  answer kosong', false);
    elseif (str_starts_with((string)$last['content'], 'Error')) report('  answer = AI error', false, $last['content']);
    else report('  answer OK', true, substr((string)$last['content'], 0, 80));
} catch (\Throwable $e) {
    report('LexQna chat ran', false, $e->getMessage());
}
session()->forget('lexqna_history');

// ==== 4. ValidityCheckerController::check (ekstrak sitasi + AI analyze) ====
try {
    $req = new Request();
    $req->setMethod('POST');
    $req->request->set('text', 'Berdasarkan Undang-Undang Nomor 11 Tahun 2008 tentang ITE, ...');
    $ctrl = new \App\Http\Controllers\Ai\ValidityCheckerController();
    $view = $ctrl->check($req);
    $data = $view->getData();
    $ai = $data['aiAnalysis'] ?? '';
    report('Validity check ran', is_object($view), 'aiAnalysis len=' . strlen((string)$ai));
    if (strlen((string)$ai) < 5) report('  AI analysis isi', false, (string)$ai);
    else report('  AI analysis isi', true, substr((string)$ai, 0, 80));
} catch (\Throwable $e) {
    report('Validity check ran', false, $e->getMessage());
}

// ==== 5. DraftController::create (generate NDA) ====
try {
    $req = new Request();
    $req->setMethod('POST');
    $req->request->set('document_type', 'nda');
    $req->request->set('instructions', 'Perusahaan teknologi PT Maju ingin melindungi data internal');
    $ctrl = new \App\Http\Controllers\Ai\DraftController();
    $view = $ctrl->create($req);
    $data = $view->getData();
    $drafts = $data['drafts'] ?? [];
    report('Draft create ran', is_object($view), 'count=' . count($drafts));
    if (empty($drafts)) report('  drafts isi', false);
    elseif (str_starts_with((string)$drafts[0], 'Error')) report('  draft = AI error', false, $drafts[0]);
    else report('  draft isi', true, 'len=' . strlen((string)$drafts[0]) . ' -> ' . substr(strip_tags((string)$drafts[0]), 0, 60));
} catch (\Throwable $e) {
    report('Draft create ran', false, $e->getMessage());
}

echo "\n=== RESULT: $pass pass, $fail fail ===\n";
