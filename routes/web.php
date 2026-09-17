<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\Billing2Controller;
use App\Http\Controllers\Billing3Controller;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\SuperAdmin\PlanController;
use App\Http\Controllers\SuperAdmin\AnalyticsController;
use App\Http\Controllers\DecisionController;
use App\Http\Controllers\RegulationController;
use App\Http\Controllers\RegulationContentController;
use App\Http\Controllers\LegalGlossaryController;
use App\Http\Controllers\ConsolidationController;
use App\Http\Controllers\PutusanController;
use App\Http\Controllers\TeamWorkspaceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\BrandingController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\WorkspaceMemberController;
use App\Http\Controllers\WorkspaceDocumentController;
use App\Http\Controllers\WorkspaceNoteController;
use App\Http\Controllers\WorkspaceTaskController;
use App\Http\Controllers\WorkspaceTimeEntryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyPublicController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Ai\LexQnaController;
use App\Http\Controllers\Ai\DraftController;
use App\Http\Controllers\Ai\ValidityCheckerController;
use App\Http\Controllers\Ai\AdvancedAiController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\OnboardingController;

Route::get('/', fn () => view('welcome'))->name('home');
Route::get('/disclaimer', fn() => view('disclaimer'))->name('disclaimer');
Route::get('/terms-of-service', fn() => view('tos'))->name('tos');
Route::get('/refund-policy', fn() => view('refund'))->name('refund-policy');
Route::get('/privacy-policy', fn() => view('privacy'))->name('privacy');
Route::get('/data-processing-agreement', fn() => view('dpa'))->name('dpa');
Route::get('/status', fn() => view('status'))->name('status');

// Profil perusahaan publik via slug (tenant branding)
Route::get('/k/{slug}', CompanyPublicController::class)->name('company.public');

// Webhook (no auth)
Route::post('/webhook/payment', [PaymentWebhookController::class, '__invoke'])->name('webhook.payment');
Route::post('/webhook/doku', [Billing3Controller::class, 'handleNotification'])->name('webhook.doku');

// Auth (guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,60');

    // Lupa password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:5,10');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update.reset')->middleware('throttle:5,10');
    Route::get('/reset-password-verify', function () { return redirect()->route('login')->with('status', 'Terlalu banyak percobaan. Coba lagi nanti.'); })->name('password.reset.throttled');
});

Route::match(['get', 'post'], '/logout', [LogoutController::class, '__invoke'])->middleware('auth')->name('logout');

// Public regulations — data regulasi bersifat publik (tanpa login)
Route::get('regulations', [RegulationController::class, 'index'])->name('regulations.index');
Route::get('regulations/{regulation}', [RegulationController::class, 'show'])->name('regulations.show')->where('regulation', '[0-9]+');
Route::get('regulations/{regulation}/pdf', [RegulationController::class, 'downloadPdf'])->name('regulations.pdf')->where('regulation', '[0-9]+');

// Verifikasi email & 2FA (harus login, tidak dibatasi status/verifikasi)
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify')->middleware('signed');
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])->name('verification.send')->middleware('throttle:3,10');

    Route::get('/2fa', [TwoFactorController::class, 'form'])->name('two-factor.form');
    Route::post('/2fa', [TwoFactorController::class, 'verify'])->name('two-factor.verify')->middleware('throttle:10,1');
    Route::post('/2fa/resend', [TwoFactorController::class, 'resend'])->name('two-factor.resend')->middleware('throttle:3,10');
});

// Authenticated
Route::middleware(['auth', 'twofactor', 'company.active', 'trial'])->group(function () {
    Route::get('/support', [SupportController::class, 'showForm'])->name('support');
    Route::post('/support', [SupportController::class, 'store'])->name('support.store')->middleware('throttle:5,60');
    Route::get('/dashboard', [DashboardController::class, '__invoke'])->name('dashboard');
    Route::post('/onboarding/dismiss', [OnboardingController::class, 'dismiss'])->name('onboarding.dismiss');

    // Billing
    Route::get('/billing', [BillingController::class, '__invoke'])->name('billing');
    Route::post('/billing/subscribe', [BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::get('/billing/success', [BillingController::class, 'success'])->name('billing.success');
    Route::post('/payment/upload-proof', [BillingController::class, 'uploadProof'])->name('payment.upload_proof');

    // Super Admin
    Route::middleware('superadmin')->group(function () {
    Route::get('/super-admin/analytics', AnalyticsController::class)->name('super-admin.analytics');
    Route::get('/super-admin/plans', [PlanController::class, 'index'])->name('super-admin.plans');
    Route::get("/super-admin/plans/edit", [PlanController::class, "editAllPrices"])->name("super-admin.plans.edit");
    Route::put("/super-admin/plans/edit", [PlanController::class, "updateAllPrices"])->name("super-admin.plans.update");
    Route::get('/super-admin/plans/create', [PlanController::class, 'create'])->name('super-admin.plans.create');
    Route::post('/super-admin/plans', [PlanController::class, 'store'])->name('super-admin.plans.store');
    Route::get('/super-admin/plans/{id}/edit', [PlanController::class, 'edit'])->name('super-admin.plans.edit.one');
    Route::put('/super-admin/plans/{id}', [PlanController::class, 'update'])->name('super-admin.plans.update.one');
    Route::delete('/super-admin/plans/{id}', [PlanController::class, 'destroy'])->name('super-admin.plans.destroy');
    Route::get('/super-admin/companies', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'index'])->name('super-admin.companies');
    Route::post('/super-admin/companies/{company}/approve', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'approve'])->name('super-admin.companies.approve');
    Route::post('/super-admin/companies/{company}/reject', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'reject'])->name('super-admin.companies.reject');
    Route::post('/super-admin/companies/{company}/suspend', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'suspend'])->name('super-admin.companies.suspend');
    Route::post('/super-admin/companies/{company}/activate', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'activate'])->name('super-admin.companies.activate');
    Route::post('/super-admin/companies/{company}/deactivate', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'deactivate'])->name('super-admin.companies.deactivate');
    Route::post('/super-admin/companies/{company}/status', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'updateStatus'])->name('super-admin.companies.status');
    });

    // Password change
    Route::get('/password-change', [PasswordController::class, 'showForm'])->name('password.change');
    Route::post('/password-change', [PasswordController::class, 'updatePassword'])->name('password.update');

    // Security (login history, sessions, 2FA)
    Route::get("/security", [SecurityController::class, "index"])->name("security.index");
    Route::delete("/security/sessions/{id}", [SecurityController::class, "revokeSession"])->name("security.revoke");
    Route::post("/security/sessions/revoke-all", [SecurityController::class, "revokeAllOtherSessions"])->name("security.revoke-all");
    Route::post("/security/two-factor/enable", [SecurityController::class, "enableTwoFactor"])->name("security.two-factor.enable");
    Route::post("/security/two-factor/disable", [SecurityController::class, "disableTwoFactor"])->name("security.two-factor.disable");

    // Billing 2 - QRIS Dinamis (testing, isolated from /billing)
    Route::get('/billing2', [Billing2Controller::class, '__invoke'])->name('billing2');
    Route::post('/billing2/make-dynamic', [Billing2Controller::class, 'makeDynamic'])->name('billing2.make-dynamic');

    // Billing 3 - DOKU Checkout (testing, isolated from /billing)
    Route::get('/billing3', [Billing3Controller::class, '__invoke'])->name('billing3');
    Route::post('/billing3/checkout', [Billing3Controller::class, 'createCheckout'])->name('billing3.checkout');

    // /regulations/{...} create/edit hanya untuk user terverifikasi
    Route::get('regulations/create', [RegulationController::class, 'create'])->name('regulations.create');
    Route::post('regulations', [RegulationController::class, 'store'])->name('regulations.store')->middleware('quota:regulations');
    Route::get('regulations/{regulation}/edit', [RegulationController::class, 'edit'])->name('regulations.edit');
    Route::put('regulations/{regulation}', [RegulationController::class, 'update'])->name('regulations.update');
    Route::delete('regulations/{regulation}', [RegulationController::class, 'destroy'])->name('regulations.destroy');
    Route::post('regulations/search-fetch', [RegulationController::class, 'searchAndFetchFromBpk'])->name('regulations.search-fetch')->middleware('quota:regulations');
    Route::post('regulations/{regulation}/refetch', [RegulationController::class, 'refetchFromBpk'])->name('regulations.refetch')->middleware('quota:regulations');

    // Core CRUD & fitur lanjutan — WAJIB email terverifikasi
    Route::middleware('verified')->group(function () {
        Route::get('decisions', [DecisionController::class, 'index'])->name('decisions');
        Route::get('decisions/courts', [DecisionController::class, 'getCourts'])->name('decisions.courts');
        Route::get('decisions/categories', [DecisionController::class, 'getCategories'])->name('decisions.categories');
        Route::get('decisions/fetch', [DecisionController::class, 'fetchDecisions'])->name('decisions.fetch');
        Route::resource('regulation-contents', RegulationContentController::class)->only(['index', 'store', 'update']);
        Route::resource('legal-glossary', LegalGlossaryController::class);
        Route::resource('consolidations', ConsolidationController::class)->only(['index', 'create', 'store', 'update']);
        Route::resource('putusans', PutusanController::class)->only(['index', 'show']);
        Route::post('putusans/{putusan}/analyze', [PutusanController::class, 'analyze'])->name('putusans.analyze')->where('putusan', '[0-9]+');
        Route::resource('team-workspaces', TeamWorkspaceController::class);
        // Workspace nested features
        Route::post('team-workspaces/{workspace}/members', [WorkspaceMemberController::class, 'store'])->name('workspace-members.store');
        Route::patch('team-workspaces/{workspace}/members/{user}', [WorkspaceMemberController::class, 'update'])->name('workspace-members.update');
        Route::delete('team-workspaces/{workspace}/members/{user}', [WorkspaceMemberController::class, 'destroy'])->name('workspace-members.destroy');
        Route::post('team-workspaces/{workspace}/documents', [WorkspaceDocumentController::class, 'store'])->name('workspace-documents.store');
        Route::get('team-workspaces/{workspace}/documents/{document}/download', [WorkspaceDocumentController::class, 'download'])->name('workspace-documents.download');
        Route::delete('team-workspaces/{workspace}/documents/{document}', [WorkspaceDocumentController::class, 'destroy'])->name('workspace-documents.destroy');
        Route::post('team-workspaces/{workspace}/notes', [WorkspaceNoteController::class, 'store'])->name('workspace-notes.store');
        Route::patch('team-workspaces/{workspace}/notes/{note}', [WorkspaceNoteController::class, 'update'])->name('workspace-notes.update');
        Route::delete('team-workspaces/{workspace}/notes/{note}', [WorkspaceNoteController::class, 'destroy'])->name('workspace-notes.destroy');
        Route::post('team-workspaces/{workspace}/tasks', [WorkspaceTaskController::class, 'store'])->name('workspace-tasks.store');
        Route::patch('team-workspaces/{workspace}/tasks/{task}', [WorkspaceTaskController::class, 'update'])->name('workspace-tasks.update');
        Route::delete('team-workspaces/{workspace}/tasks/{task}', [WorkspaceTaskController::class, 'destroy'])->name('workspace-tasks.destroy');
        Route::patch('team-workspaces/{workspace}/tasks/{task}/toggle', [WorkspaceTaskController::class, 'toggleStatus'])->name('workspace-tasks.toggle');
        Route::post('team-workspaces/{workspace}/time-entries', [WorkspaceTimeEntryController::class, 'store'])->name('workspace-time.store');
        Route::delete('team-workspaces/{workspace}/time-entries/{entry}', [WorkspaceTimeEntryController::class, 'destroy'])->name('workspace-time.destroy');
        // SaaS Layer Routes
        Route::get("/notifications", [NotificationController::class, "index"])->name("notifications.index");
        Route::post("/notifications/{id}/read", [NotificationController::class, "read"])->name("notifications.read");
        Route::post("/notifications/read-all", [NotificationController::class, "readAll"])->name("notifications.read.all");
        Route::get("/search", [SearchController::class, "index"])->name("search");
        Route::get("/reports", [ReportsController::class, "index"])->name("reports.index");
        Route::get("/reports/{workspace}", [ReportsController::class, "workspace"])->name("reports.workspace");
        Route::get("/settings/branding", [BrandingController::class, "edit"])->name("branding.edit");
        Route::put("/settings/branding", [BrandingController::class, "update"])->name("branding.update");
        Route::resource('companies', CompanyController::class)->only(['index', 'show']);
        Route::resource('users', UserController::class)->only(['index', 'show']);
        Route::resource('audit-logs', AuditLogController::class)->only(['index', 'show']);

        // AI Advanced Modules (dibatasi kuota)
        Route::get('/ai/lex-qna', [LexQnaController::class, 'form'])->name('ai.lex-qna.form');
        Route::post('/ai/lex-qna', [LexQnaController::class, 'chat'])->name('ai.lex-qna')->middleware('quota:qna');
        Route::post('/ai/lex-qna/clear', [LexQnaController::class, 'clear'])->name('ai.lex-qna.clear');
        Route::get('/ai/draft', [DraftController::class, 'form'])->name('ai.draft.form');
        Route::post('/ai/draft', [DraftController::class, 'create'])->name('ai.draft')->middleware('quota:draft');
        Route::match(['get', 'post'], '/ai/draft/download', [DraftController::class, 'download'])->name('ai.draft.download');
        Route::get('/ai/validity', [ValidityCheckerController::class, 'form'])->name('ai.validity.form');
        Route::post('/ai/validity', [ValidityCheckerController::class, 'check'])->name('ai.validity')->middleware('quota:validity');
        Route::match(['get', 'post'], '/ai/contract-review', [AdvancedAiController::class, 'contractReview'])->name('ai.contract-review')->middleware(['throttle:30,1', 'quota:contract_review']);
        Route::post("ai/contract-review/download", [AdvancedAiController::class, "downloadResult"])->name("ai.contract-review.download");
        Route::post('/pdf/upload', [PdfController::class, 'upload'])->name('pdf.upload');
        Route::post('/pdf/parse-text', [PdfController::class, 'parseText'])->name('pdf.parse-text');
    });
});