<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMenuController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\CashierController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\Kasir\KasirDashboardController;
use App\Http\Controllers\Kasir\TableStatusController;
use App\Http\Controllers\Kasir\CustomerController;
use App\Http\Controllers\Kasir\PromotionController as kasirPromo;
use App\Http\Controllers\Kasir\KasirReportController;
use App\Http\Controllers\Kasir\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CustomerLoyaltyController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\HomeController;

Route::get('/', [HomeController::class, 'index'])
    ->middleware('auth');

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login')
    ->middleware('guest');

Route::post('/login', [LoginController::class, 'login'])
    ->middleware('guest');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// Semua butuh login
Route::middleware('auth')->group(function () {

    // OWNER
    Route::middleware('role:owner')->group(function () {
        Route::get('/owner/dashboard', [OwnerDashboardController::class, 'index'])
            ->name('owner.dashboard');

        Route::get('/owner/outlets', fn () => 'Owner: Daftar Outlet')->name('owner.outlets.index');
        Route::get('/owner/users', fn () => 'Owner: Daftar User')->name('owner.users.index');
        Route::get('/owner/menu', fn () => 'Owner: Daftar Menu')->name('owner.menu.index');
        Route::get('/owner/ingredients', fn () => 'Owner: Bahan Baku')->name('owner.ingredients.index');
         Route::get('/owner/reports/sales', [ReportController::class, 'salesOwner'])
        ->name('owner.reports.sales');

        Route::get('/owner/reports/sales/export', [ReportController::class, 'salesOwnerExport'])
            ->name('owner.reports.sales.export');

        // History loyalty per customer
        Route::get('/owner/customers/{customer}/loyalty', [CustomerLoyaltyController::class, 'show'])
            ->name('owner.customers.loyalty');

        // Route::get('/owner/reports/top-menus/export', [TopMenuReportController::class, 'exportOwner'])
        // ->name('owner.reports.top-menus.export');
    });

    // ADMIN
    Route::middleware(['role:admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

             // Dashboard
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])
                ->name('dashboard');

            Route::get('/dashboard/{order}/items', [AdminDashboardController::class, 'items'])
            ->name('dashboard.items');


            // MENU (sudah ada)
            Route::resource('menu', AdminMenuController::class);
            Route::get('menu/next-code', [AdminMenuController::class, 'nextCode'])
            ->name('menu.next-code');
            // 📦 Bahan Baku
            Route::resource('ingredients', IngredientController::class);

            // Stock In/Out + Riwayat
           Route::resource('stock-movements', StockMovementController::class)
            ->names('stock-movements')
            ->except(['show']);

            // 🪑 Meja
            Route::resource('tables', TableController::class);

            // 👥 Kasir
            Route::resource('cashiers', CashierController::class);
            Route::post('cashiers/{cashier}/reset-password', [CashierController::class,'resetPassword'])->name('cashiers.reset-password');

            // 📊 Laporan Penjualan
            Route::get('reports/sales', [ReportController::class, 'salesAdmin'])
            ->name('reports.sales');
            Route::get('reports/sales/export', [ReportController::class, 'salesAdminExport'])
                ->name('reports.sales.export');
            // 👥 Loyalty per customer (admin)
            Route::get('/customers/{customer}/loyalty', [CustomerLoyaltyController::class, 'show'])
                ->name('customers.loyalty');

            Route::resource('/promotions', PromotionController::class)
                ->names('promotions');
        });


    // KASIR
    Route::middleware('role:kasir')->group(function () {
      
        Route::get('kasir/dashboard', [KasirDashboardController::class, 'index'])
            ->name('kasir.dashboard');

        Route::get('kasir/dashboard/{order}/items', [AdminDashboardController::class, 'items'])
            ->name('kasir.dashboard.items');

        // POS / Order

        Route::put('kasir/orders/{order}/reserved',[OrderController::class, 'updateReserved'])
            ->name('kasir.orders.updateReserved');

        Route::resource('kasir/orders', OrderController::class)
            ->names('kasir.orders');


        // Bayar order (open → paid)
        Route::post('kasir/orders/{order}/pay', [OrderController::class, 'pay'])
            ->name('kasir.orders.pay');

        Route::get('/kasir/orders/{order}/print',[OrderController::class, 'print'])
            ->name('kasir.orders.print');

        Route::get('/kasir/orders/{order}/after-pay',[OrderController::class, 'afterPay'])
            ->name('kasir.orders.afterPay');

        // Status meja
        Route::get('kasir/tables', [TableStatusController::class, 'index'])
            ->name('kasir.tables.index');

        Route::post('/tables/update-status', [TableStatusController::class, 'updateStatus'])
            ->name('kasir.tables.updateStatus');


        // Customer & Loyalty
        Route::get('kasir/customers', [CustomerController::class, 'index'])
            ->name('kasir.customers.index');
        Route::get('kasir/customers/{customer}', [CustomerController::class, 'show'])
            ->name('kasir.customers.show');
        // ➜ JSON poin untuk JS POS
        Route::get('kasir/customers/{customer}/points', [CustomerController::class, 'points'])
            ->name('kasir.customers.points');

        // Promo aktif (read-only)
        Route::get('kasir/promotions', [kasirPromo::class, 'index'])
            ->name('kasir.promotions.index');

        // Laporan kasir (hari ini)
        Route::get('kasir/reports/today', [KasirReportController::class, 'today'])
            ->name('kasir.reports.today');
    });

});

