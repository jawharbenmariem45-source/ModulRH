<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\ContractTypeController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\EmployerAuthController;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\EmployerDashboardController;
use Illuminate\Support\Facades\Route;

// ── Authentification publique ─────────────────────────────
Route::get('/', [AuthController::class, 'login'])->name('login');
Route::post('/', [AuthController::class, 'handleLogin'])->name('handleLogin');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/validate-account/{email}', [AdminController::class, 'defineAccess']);
Route::post('/validate-account/{email}', [AdminController::class, 'submitDefineAccess'])->name('submitDefineAccess');

// ── Dashboard ─────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AppController::class, 'index'])->name('dashboard');
});

// ── Administrateurs ───────────────────────────────────────
Route::middleware(['auth', 'can:view roles'])->group(function () {
    Route::prefix('administrateurs')->name('administrateurs.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/create', [AdminController::class, 'create'])->name('create');
        Route::get('/edit/{administrateur}', [AdminController::class, 'edit'])->name('edit');
        Route::put('/edit/{administrateur}', [AdminController::class, 'update'])->name('update');
        Route::post('/store', [AdminController::class, 'store'])->name('store');
        Route::post('/delete/{user}', [AdminController::class, 'delete'])->name('delete');
    });
});

// ── Configurations ────────────────────────────────────────
Route::middleware(['auth', 'can:view settings'])->prefix('configurations')->group(function () {
    Route::get('/', [ConfigurationController::class, 'index'])->name('configurations');
    Route::post('/save', [ConfigurationController::class, 'save'])->name('configurations.save');
});

// ── Permissions & Rôles ───────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::put('/{user}', [PermissionController::class, 'updateUser'])->name('updateUser');
        Route::get('/manage', [PermissionController::class, 'managePermissions'])->name('manage');
        Route::get('/create', [PermissionController::class, 'createPermission'])->name('create');
        Route::post('/store', [PermissionController::class, 'storePermission'])->name('store');
        Route::get('/edit/{permission}', [PermissionController::class, 'editPermission'])->name('edit');
        Route::put('/update/{permission}', [PermissionController::class, 'updatePermission'])->name('update');
        Route::delete('/delete/{permission}', [PermissionController::class, 'deletePermission'])->name('delete');
    });

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/manage', [PermissionController::class, 'manageRoles'])->name('manage');
        Route::get('/create', [PermissionController::class, 'createRole'])->name('create');
        Route::post('/store', [PermissionController::class, 'storeRole'])->name('store');
        Route::get('/edit/{role}', [PermissionController::class, 'editRole'])->name('edit');
        Route::put('/update/{role}', [PermissionController::class, 'updateRole'])->name('update');
        Route::delete('/delete/{role}', [PermissionController::class, 'deleteRole'])->name('delete');
    });
});

// ── Employers ─────────────────────────────────────────────
Route::middleware(['auth', 'can:view employers'])->prefix('employer')->name('employer.')->group(function () {
    Route::get('/', [EmployerController::class, 'index'])->name('index');
    Route::get('/create', [EmployerController::class, 'create'])->name('create');
    Route::get('/edit/{employer}', [EmployerController::class, 'edit'])->name('edit');
    Route::post('/store', [EmployerController::class, 'store'])->name('store');
    Route::put('/update/{employer}', [EmployerController::class, 'update'])->name('update');
    Route::get('/delete/{employer}', [EmployerController::class, 'delete'])->name('delete');
});

// ── Contrats employers ────────────────────────────────────
Route::middleware(['auth', 'can:view contracts'])->prefix('contrats')->name('contrat.')->group(function () {
    Route::get('/', [ContratController::class, 'index'])->name('index');
    Route::post('/store', [ContratController::class, 'store'])->name('store');
    Route::get('/edit/{employer}', [ContratController::class, 'edit'])->name('edit');
    Route::put('/update/{employer}', [ContratController::class, 'update'])->name('update');
    Route::get('/delete/{employer}', [ContratController::class, 'delete'])->name('delete');
    Route::get('/pdf/{employer}', [ContratController::class, 'downloadPdf'])->name('pdf');
});

// ── Types contrats ────────────────────────────────────────
Route::middleware('auth')->prefix('admin/contracts')->name('contracts.')->group(function () {
    Route::get('/', [ContractTypeController::class, 'index'])->name('index');
    Route::post('/store', [ContractTypeController::class, 'store'])->name('store');
    Route::put('/update/{contract}', [ContractTypeController::class, 'update'])->name('update');
    Route::delete('/delete/{contract}', [ContractTypeController::class, 'destroy'])->name('destroy');
    Route::patch('/{contract}/toggle', [ContractTypeController::class, 'toggle'])->name('toggle');
});

// ── Paiements ─────────────────────────────────────────────
Route::middleware(['auth', 'can:view payments'])->group(function () {
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
    Route::post('/payment/init', [PaymentController::class, 'initPayment'])->name('payment.init');
    Route::get('/payment/download-invoice/{payment}', [PaymentController::class, 'download_invoice'])->name('payment.download');
    Route::get('/payment/preview-invoice/{payment}', [PaymentController::class, 'preview_invoice'])->name('payment.preview');
});

// ── Pointage RH ───────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/pointage', [PointageController::class, 'adminIndex'])->name('pointage.admin');
});

// ── Départements ──────────────────────────────────────────
Route::middleware(['auth', 'can:view departments'])->prefix('departements')->name('departement.')->group(function () {
    Route::get('/', [DepartementController::class, 'index'])->name('index');
    Route::get('/create', [DepartementController::class, 'create'])->name('create');
    Route::post('/create', [DepartementController::class, 'store'])->name('store');
    Route::get('/edit/{departement}', [DepartementController::class, 'edit'])->name('edit');
    Route::put('/update/{departement}', [DepartementController::class, 'update'])->name('update');
    Route::get('/{departement}', [DepartementController::class, 'destroy'])->name('destroy');
});

// ── Congés ────────────────────────────────────────────────
Route::middleware(['auth', 'can:view leaves'])->prefix('conges')->name('conge.')->group(function () {
    Route::get('/', [CongeController::class, 'index'])->name('index');
    Route::get('/create', [CongeController::class, 'create'])->name('create');
    Route::post('/store', [CongeController::class, 'store'])->name('store');
    Route::patch('/{id}/accepter', [CongeController::class, 'accepter'])->name('accepter');
    Route::patch('/{id}/rejeter', [CongeController::class, 'rejeter'])->name('rejeter');
});

// ── Espace employé ────────────────────────────────────────
Route::prefix('espace-employe')->name('employer_space.')->group(function () {

    Route::get('/login', [EmployerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [EmployerAuthController::class, 'login'])->name('handleLogin');
    Route::get('/validate-account/{email}', [EmployerAuthController::class, 'showDefinePassword'])->name('definePassword');
    Route::post('/validate-account/{email}', [EmployerAuthController::class, 'submitDefinePassword'])->name('submitDefinePassword');

    Route::middleware('auth:employer')->group(function () {
        Route::post('/logout', [EmployerAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [EmployerDashboardController::class, 'dashboard'])->name('dashboard');

        Route::get('/paiements', [EmployerDashboardController::class, 'paiements'])->name('paiements');
        Route::get('/paiements/pdf/{payment}', [EmployerDashboardController::class, 'downloadPaiement'])->name('paiements.pdf');
        Route::get('/paiements/preview/{payment}', [EmployerDashboardController::class, 'previewPaiement'])->name('paiements.preview');

        Route::get('/conges', [EmployerDashboardController::class, 'conges'])->name('conges');
        Route::get('/conges/create', [EmployerDashboardController::class, 'createConge'])->name('conges.create');
        Route::post('/conges/store', [EmployerDashboardController::class, 'storeConge'])->name('conges.store');
        Route::get('/conges/edit/{conge}', [EmployerDashboardController::class, 'editConge'])->name('conges.edit');
        Route::put('/conges/update/{conge}', [EmployerDashboardController::class, 'updateConge'])->name('conges.update');
        Route::delete('/conges/delete/{conge}', [EmployerDashboardController::class, 'deleteConge'])->name('conges.delete');

        Route::get('/contrat', [EmployerDashboardController::class, 'contrat'])->name('contrat');

        Route::get('/pointage', [PointageController::class, 'index'])->name('pointage.index');
        Route::post('/pointage/check-in-matin', [PointageController::class, 'checkInMatin'])->name('pointage.check_in_matin');
        Route::post('/pointage/check-out-matin', [PointageController::class, 'checkOutMatin'])->name('pointage.check_out_matin');
        Route::post('/pointage/check-in-apres-midi', [PointageController::class, 'checkInApresMidi'])->name('pointage.check_in_apres_midi');
        Route::post('/pointage/check-out-apres-midi', [PointageController::class, 'checkOutApresMidi'])->name('pointage.check_out_apres_midi');
    });
});