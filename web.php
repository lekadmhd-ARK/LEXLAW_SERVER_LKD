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

Route::get('/', fn () => view('welcome'))->name('home');

// Auth (guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LogoutController::class, '__invoke'])->middleware('auth')->name('logout');

// Authenticated
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, '__invoke'])->name('dashboard');

    // Billing
    Route::get('/billing', [BillingController::class, '__invoke'])->name('billing');
    Route::post('/billing/subscribe', [BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::post('/webhook/payment', [PaymentWebhookController::class, '__invoke'])->name('webhook.payment');

    // Super Admin
    Route::get('/super-admin/plans', [PlanController::class, 'index'])->name('super-admin.plans');

    // Core CRUD
    Route::resource('regulations', RegulationController::class);
    Route::resource('regulation-contents', RegulationContentController::class)->only(['index', 'store', 'update']);
    Route::resource('legal-glossary', LegalGlossaryController::class);
    Route::resource('consolidations', ConsolidationController::class)->only(['index', 'store', 'update']);
    Route::resource('team-workspaces', TeamWorkspaceController::class)->only(['index', 'store']);
    Route::resource('companies', CompanyController::class)->only(['index', 'show']);
    Route::resource('users', UserController::class)->only(['index', 'show']);
    Route::resource('audit-logs', AuditLogController::class)->only(['index', 'show']);

    // AI
    Route::post('/ai/lex-qna', [LexQnaController::class, 'chat'])->name('ai.lex-qna');
    Route::post('/ai/draft', [DraftController::class, 'create'])->name('ai.draft');
    Route::get('/ai/draft/{id}/download', [DraftController::class, 'download'])->name('ai.draft.download');
});
