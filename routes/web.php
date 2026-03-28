<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\KdsController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\VariationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('auth')->group(function () {
    
    // ==========================================
    // DISPATCHER ROUTE
    // ==========================================
    Route::get('/', function () {
        $role = auth()->user()->role;
        if ($role === 'owner') {
            return redirect()->route('dashboard');
        } elseif ($role === 'cashier') {
            return redirect()->route('pos.index');
        } elseif ($role === 'kitchen') {
            return redirect()->route('kds.index');
        }
        return abort(403);
    })->name('home');

    // ==========================================
    // OWNER ONLY ROUTES
    // ==========================================
    Route::middleware('role:owner')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Products
        Route::resource('products', ProductController::class);
        Route::post('products/{product}/toggle-active', [ProductController::class, 'toggleActive'])->name('products.toggle-active');
        
        // Categories
        Route::resource('categories', CategoryController::class)->only(['index', 'store']);
        
        // Raw Materials
        Route::resource('raw-materials', RawMaterialController::class)->except(['create', 'edit', 'show']);

        // Variations
        Route::resource('variations', VariationController::class)->except(['create', 'edit', 'show']);
        
        // Vouchers
        Route::resource('vouchers', VoucherController::class)->except(['show']);
        
        // Order History
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/import', [TransactionController::class, 'importForm'])->name('transactions.import');
        Route::post('transactions/import', [TransactionController::class, 'importStore'])->name('transactions.import.store');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::patch('transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.status');
        Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

        // Finance & Reporting
        Route::get('finance', [FinanceController::class, 'dashboard'])->name('finance.dashboard');
        Route::get('finance/profit-loss', [FinanceController::class, 'profitLoss'])->name('finance.profit-loss');

        // Expenses
        Route::resource('expenses', ExpenseController::class)->except(['show', 'create', 'edit']);

        // Wastages
        Route::get('wastages', [ExpenseController::class, 'wastageIndex'])->name('wastages.index');
        Route::post('wastages', [ExpenseController::class, 'storeWastage'])->name('wastages.store');
        Route::delete('wastages/{wastage}', [ExpenseController::class, 'destroyWastage'])->name('wastages.destroy');

        // Expense Categories API
        Route::get('expense-categories', [ExpenseController::class, 'categories'])->name('expense-categories.index');
        Route::post('expense-categories', [ExpenseController::class, 'storeCategory'])->name('expense-categories.store');

        // User Management
        Route::resource('users', UserController::class)->names('admin.users');
    });

    // ==========================================
    // OWNER & CASHIER ROUTES
    // ==========================================
    Route::middleware('role:owner,cashier')->group(function () {
        // Point of Sale
        Route::get('pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('pos/checkout', [TransactionController::class, 'store'])->name('pos.checkout');
        Route::post('pos/qris/generate', [PosController::class, 'generateQris'])->name('pos.qris.generate');
        Route::get('pos/refresh-stock', [PosController::class, 'refreshStock'])->name('pos.refresh-stock');
        Route::post('pos/vouchers/apply', [\App\Http\Controllers\VoucherController::class, 'validateVoucher'])->name('pos.vouchers.apply');
        Route::get('transactions/{transaction}/receipt', [TransactionController::class, 'showReceipt'])->name('transactions.receipt');
        Route::get('transactions/{transaction}/receipt-data', [TransactionController::class, 'receiptData'])->name('transactions.receipt-data');
        Route::patch('transactions/{transaction}/payment', [TransactionController::class, 'confirmPayment'])->name('transactions.payment.confirm');

        // Cash Register / Shift Management
        Route::get('pos/shift', [CashRegisterController::class, 'index'])->name('pos.shift.index');
        Route::get('pos/shift/history', [CashRegisterController::class, 'history'])->name('pos.shift.history');
        Route::get('pos/shift/open-form', [CashRegisterController::class, 'openForm'])->name('pos.shift.open-form');
        Route::post('pos/shift/open', [CashRegisterController::class, 'openShift'])->name('pos.shift.open');
        Route::get('pos/shift/summary', [CashRegisterController::class, 'getSummary'])->name('pos.shift.summary');
        Route::post('pos/shift/movement', [CashRegisterController::class, 'addCashMovement'])->name('pos.shift.movement');
        Route::post('pos/shift/close', [CashRegisterController::class, 'closeShift'])->name('pos.shift.close');
        Route::get('pos/shift/{shift}', [CashRegisterController::class, 'show'])->name('pos.shift.show');
        Route::get('pos/shift/active', [CashRegisterController::class, 'getActiveShift']);
    });

    // ==========================================
    // OWNER & KITCHEN ROUTES
    // ==========================================
    Route::middleware('role:owner,kitchen')->group(function () {
        // Kitchen Display System (KDS)
        Route::get('kds', [KdsController::class, 'index'])->name('kds.index');
        Route::get('api/kds/orders', [KdsController::class, 'getPendingOrders']);
        Route::post('api/kds/orders/{transaction}/complete', [KdsController::class, 'markAsComplete']);
    });

});

require __DIR__.'/auth.php';
