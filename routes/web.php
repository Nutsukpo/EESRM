
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\ActivityLogController;

use App\Http\Middleware\LogUserActivity;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes With Activity Logging
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', LogUserActivity::class])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [LoginController::class, 'destroy'])
        ->name('logout');

    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */

    Route::get('/users', [UserController::class, 'index'])
        ->name('users.index');

    Route::get('/users/create', [UserController::class, 'create'])
        ->name('users.create');

    Route::post('/users', [UserController::class, 'store'])
        ->name('users.store');

    /*
    |--------------------------------------------------------------------------
    | Menu Categories
    |--------------------------------------------------------------------------
    */

    Route::resource('categories', MenuCategoryController::class)
        ->except(['show', 'destroy'])
        ->names('categories');

    Route::patch(
        '/categories/{category}/toggle-status',
        [MenuCategoryController::class, 'toggleStatus']
    )->name('categories.toggle-status');

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    */

    Route::resource('menu-items', MenuItemController::class)
        ->except(['show', 'destroy'])
        ->names('menu-items');

    Route::patch(
        '/menu-items/{menu_item}/toggle-availability',
        [MenuItemController::class, 'toggleAvailability']
    )->name('menu-items.toggle-availability');

    /*
    |--------------------------------------------------------------------------
    | Point of Sale
    |--------------------------------------------------------------------------
    */

    Route::get('/pos', [PosController::class, 'index'])
        ->name('pos.index');

    Route::post('/pos/orders', [PosController::class, 'store'])
        ->name('pos.store');

    /*
    |--------------------------------------------------------------------------
    | Order Management
    |--------------------------------------------------------------------------
    */

    Route::get('/orders', [OrderController::class, 'index'])
        ->name('orders.index');

    Route::get('/orders/{order}', [OrderController::class, 'show'])
        ->name('orders.show');

    Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])
        ->name('orders.edit');

    Route::put('/orders/{order}', [OrderController::class, 'update'])
        ->name('orders.update');

    /*
    |--------------------------------------------------------------------------
    | Payment Management
    |--------------------------------------------------------------------------
    */

    Route::get('/payments', [PaymentController::class, 'index'])
        ->name('payments.index');

    Route::get(
        '/orders/{order}/payments/create',
        [PaymentController::class, 'create']
    )->name('payments.create');

    Route::post(
        '/orders/{order}/payments',
        [PaymentController::class, 'store']
    )->name('payments.store');

    Route::get(
        '/payments/{payment}/receipt',
        [PaymentController::class, 'receipt']
    )->name('payments.receipt');

    /*
    |--------------------------------------------------------------------------
    | Sales Reports
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/reports/daily-sales',
        [SalesReportController::class, 'daily']
    )->name('reports.daily-sales');

    Route::get(
        '/reports/monthly-sales',
        [SalesReportController::class, 'monthly']
    )->name('reports.monthly-sales');

    /*
    |--------------------------------------------------------------------------
    | Activity Logs
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/activity-logs',
        [ActivityLogController::class, 'index']
    )->name('activity-logs.index');

    Route::get(
        '/activity-logs/{log}',
        [ActivityLogController::class, 'show']
    )->name('activity-logs.show');
});
