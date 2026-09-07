<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\Billing2Controller;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\SuperAdmin\PlanController;
use App\Http\Controllers\DecisionController;
use App\Http\Controllers\RegulationController;
use App\Http\Controllers\RegulationContentController;
use App\Http\Controllers\LegalGlossaryController;
use App\Http\Controllers\ConsolidationController;
use App\Http\Controllers\TeamWorkspaceController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Ai\LexQnaController;
use App\Http\Controllers\Ai\DraftController;
use App\Http\Controllers\Ai\ValidityCheckerController;
use App\Http\Controllers\Ai\AdvancedAiController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\Auth\PasswordController;

Route::get('/', fn () => view('welcome'))->name('home');
Route::get('/disclaimer', fn() => view('disclaimer'))->name('disclaimer');
Route::get('/terms-of-service', fn() => view('tos'))->name('tos');
Route::get('/refund-policy', fn() => view('refund'))->name('refund-policy');

// Webhook (no auth)
Route::post('/webhook/payment', [PaymentWebhookController::class, '__invoke'])->name('webhook.payment');

// Auth (guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::match(['get', 'post'], '/logout', [LogoutController::class, '__invoke'])->middleware('auth')->name('logout');

// Public regulations — data regulasi bersifat publik (tanpa login)
Route::get('regulations', [RegulationController::class, 'index'])->name('regulations.index');
Route::get('regulations/{regulation}', [RegulationController::class, 'show'])->name('regulations.show')->where('regulation', '[0-9]+');
Route::get('regulations/{regulation}/pdf', [RegulationController::class, 'downloadPdf'])->name('regulations.pdf')->where('regulation', '[0-9]+');

// Authenticated
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, '__invoke'])->name('dashboard');

    // Billing
    Route::get('/billing', [BillingController::class, '__invoke'])->name('billing');
    Route::post('/billing/subscribe', [BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::get('/billing/success', [BillingController::class, 'success'])->name('billing.success');
    Route::post('/payment/upload-proof', [BillingController::class, 'uploadProof'])->name('payment.upload_proof');

    // Super Admin
    Route::get('/super-admin/plans', [PlanController::class, 'index'])->name('super-admin.plans');
    Route::get("/super-admin/plans/edit", [PlanController::class, "edit"])->name("super-admin.plans.edit");
    Route::put("/super-admin/plans/edit", [PlanController::class, "update"])->name("super-admin.plans.update");
    Route::get('/super-admin/companies', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'index'])->name('super-admin.companies');
    Route::post('/super-admin/companies/{company}/approve', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'approve'])->name('super-admin.companies.approve');
    Route::post('/super-admin/companies/{company}/reject', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'reject'])->name('super-admin.companies.reject');

    // Core CRUD
    Route::get('regulations/create', [RegulationController::class, 'create'])->name('regulations.create');
    Route::post('regulations', [RegulationController::class, 'store'])->name('regulations.store');
    Route::get('regulations/{regulation}/edit', [RegulationController::class, 'edit'])->name('regulations.edit');
    Route::put('regulations/{regulation}', [RegulationController::class, 'update'])->name('regulations.update');
    Route::delete('regulations/{regulation}', [RegulationController::class, 'destroy'])->name('regulations.destroy');
    Route::post('regulations/search-fetch', [RegulationController::class, 'searchAndFetchFromBpk'])->name('regulations.search-fetch');
    Route::post('regulations/{regulation}/refetch', [RegulationController::class, 'refetchFromBpk'])->name('regulations.refetch');
    Route::get('decisions', [DecisionController::class, 'index'])->name('decisions');
    Route::get('decisions/courts', [DecisionController::class, 'getCourts'])->name('decisions.courts');
    Route::get('decisions/categories', [DecisionController::class, 'getCategories'])->name('decisions.categories');
    Route::get('decisions/fetch', [DecisionController::class, 'fetchDecisions'])->name('decisions.fetch');
    Route::post('regulations/fetch-jdih', [RegulationController::class, 'fetchFromJdihUrl'])->name('regulations.fetch-jdih')->middleware('throttle:30,1');
    Route::resource('regulation-contents', RegulationContentController::class)->only(['index', 'store', 'update']);
    Route::resource('legal-glossary', LegalGlossaryController::class);
    Route::resource('consolidations', ConsolidationController::class)->only(['index', 'store', 'update']);
    Route::resource('team-workspaces', TeamWorkspaceController::class)->only(['index', 'store']);
    Route::resource('companies', CompanyController::class)->only(['index', 'show']);
    Route::resource('users', UserController::class)->only(['index', 'show']);
    Route::resource('audit-logs', AuditLogController::class)->only(['index', 'show']);

    // AI Advanced Modules
    Route::get('/ai/lex-qna', [LexQnaController::class, 'form'])->name('ai.lex-qna.form');
    Route::post('/ai/lex-qna', [LexQnaController::class, 'chat'])->name('ai.lex-qna');
    Route::post('/ai/lex-qna/clear', [LexQnaController::class, 'clear'])->name('ai.lex-qna.clear');
    Route::get('/ai/draft', [DraftController::class, 'form'])->name('ai.draft.form');
    Route::post('/ai/draft', [DraftController::class, 'create'])->name('ai.draft');
    Route::match(['get', 'post'], '/ai/draft/download', [DraftController::class, 'download'])->name('ai.draft.download');
    Route::get('/ai/validity', [ValidityCheckerController::class, 'form'])->name('ai.validity.form');
    Route::post('/ai/validity', [ValidityCheckerController::class, 'check'])->name('ai.validity');
    Route::post('/ai/analyze', [AdvancedAiController::class, 'analyze'])->name('ai.analyze');
    Route::match(['get', 'post'], '/ai/contract-review', [AdvancedAiController::class, 'contractReview'])->name('ai.contract-review')->middleware('throttle:30,1');
    Route::post("ai/contract-review/download", [AdvancedAiController::class, "downloadResult"])->name("ai.contract-review.download");
    Route::post('/ai/precedent-matching', [AdvancedAiController::class, 'precedentMatching'])->name('ai.precedent-matching');
    Route::post('/pdf/upload', [PdfController::class, 'upload'])->name('pdf.upload');
    Route::post('/pdf/parse-text', [PdfController::class, 'parseText'])->name('pdf.parse-text');

    // Password change
    Route::get('/password-change', [PasswordController::class, 'showForm'])->name('password.change');
    Route::post('/password-change', [PasswordController::class, 'updatePassword'])->name('password.update');

    // Billing 2 - QRIS Dinamis (testing, isolated from /billing)
    Route::get('/billing2', [Billing2Controller::class, '__invoke'])->name('billing2');
    Route::post('/billing2/make-dynamic', [Billing2Controller::class, 'makeDynamic'])->name('billing2.make-dynamic');
});
