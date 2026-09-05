<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\SuperAdmin\PlanController;
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

// Authenticated
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, '__invoke'])->name('dashboard');

    // Billing
    Route::get('/billing', [BillingController::class, '__invoke'])->name('billing');
    Route::post('/billing/subscribe', [BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::get('/billing/success', [BillingController::class, 'success'])->name('billing.success');
    Route::post('/payment/upload-qris', [BillingController::class, 'upload_qris'])->name('payment.upload_qris');

    // Super Admin
    Route::get('/super-admin/plans', [PlanController::class, 'index'])->name('super-admin.plans');
    Route::get("/super-admin/plans/edit", [PlanController::class, "edit"])->name("super-admin.plans.edit");
    Route::put("/super-admin/plans/edit", [PlanController::class, "update"])->name("super-admin.plans.update");

    // Core CRUD
    Route::resource('regulations', RegulationController::class);
    Route::get('regulations/{regulation}/pdf', [RegulationController::class, 'downloadPdf'])->name('regulations.pdf');
    Route::post('regulations/fetch-jdih', [RegulationController::class, 'fetchFromJdihUrl'])->name('regulations.fetch-jdih')->middleware('throttle:5,1');
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
    Route::match(['get', 'post'], '/ai/contract-review', [AdvancedAiController::class, 'contractReview'])->name('ai.contract-review')->middleware('throttle:5,1');
    Route::post("ai/contract-review/download", [AdvancedAiController::class, "downloadResult"])->name("ai.contract-review.download");
    Route::post('/ai/precedent-matching', [AdvancedAiController::class, 'precedentMatching'])->name('ai.precedent-matching');
    Route::post('/pdf/upload', [PdfController::class, 'upload'])->name('pdf.upload');
    Route::post('/pdf/parse-text', [PdfController::class, 'parseText'])->name('pdf.parse-text');

    // Password change
    Route::get('/disclaimer', fn() => view('disclaimer'))->name('disclaimer');
    Route::get('/terms-of-service', fn() => view('tos'))->name('tos');
    Route::get('/password-change', [PasswordController::class, 'showForm'])->name('password.change');
    Route::post('/password-change', [PasswordController::class, 'updatePassword'])->name('password.update');
});