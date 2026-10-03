<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\SettingsController;

// Public Routes (چوونەژوورەوە و چووندەرەوە)
Route::get('/login', [AuthController::class, 'show'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:5,1'])->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Protected Routes (پێویستی بە چوونەژوورەوە هەیە)
Route::middleware('auth')->group(function () {
    
    // Dashboard & Home
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS & Sales
    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::get('/pos/search', [PosController::class, 'search'])->middleware('throttle:120,1')->name('pos.search');
    Route::post('/pos', [PosController::class, 'store'])->middleware('throttle:60,1')->name('pos.store');
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::post('/sales/{sale}/return', [ReturnController::class, 'store'])->middleware('throttle:60,1')->name('returns.store');

    // Resources
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('customers', CustomerController::class)->except(['show']);

    // Customers Ledger & Payments
    Route::get('/customers/{customer}/ledger', [CustomerController::class, 'ledger'])->name('customers.ledger');
    Route::post('/payments', [PaymentController::class, 'store'])->middleware('throttle:60,1')->name('payments.store');

    // Stock Management
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
    Route::get('/debts', [PaymentController::class, 'debts'])->name('debts.index');

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/profit', [ReportController::class, 'profit'])->name('reports.profit');
        Route::get('/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('/debts', [ReportController::class, 'debts'])->name('reports.debts');
    });

    // Admin Only Routes
    Route::middleware('role:Super Admin,Admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        
        // Backup
        Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
        Route::post('/backup', [BackupController::class, 'create'])->middleware('throttle:3,10')->name('backup.create');
        Route::get('/backup/download/{file}', [BackupController::class, 'download'])->name('backup.download');
        Route::delete('/backup/{file}', [BackupController::class, 'destroy'])->name('backup.destroy');
        
        // Settings
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});