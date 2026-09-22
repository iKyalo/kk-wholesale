<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\TransfersController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\StoresController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ReportsController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/dashboard');

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->controller(AuthController::class)->group(function () {

    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->name('login.store');

    Route::get('/register', 'showRegister')->name('register');
    Route::post('/register', 'register')->name('register.store');

});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Sales
    |--------------------------------------------------------------------------
    */

    Route::prefix('sales')
        ->name('sales.')
        ->controller(SalesController::class)
        ->group(function () {

            Route::get('/', 'index')->name('index');

            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');

            // Sales analytics
            Route::get('/by-store', 'byStore')->name('by-store');
            Route::get('/by-product', 'byProduct')->name('by-product');
            Route::get('/by-branch', 'byBranch')->name('by-branch');

        });


    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */

    Route::prefix('inventory')
        ->name('inventory.')
        ->controller(InventoryController::class)
        ->group(function () {

            Route::get('/', 'index')->name('index');

            Route::get('/by-store', 'byStore')->name('by-store');

            Route::get('/edit-stock', 'editStock')->name('edit-stock');

            Route::post('/edit-stock', 'editStock')->name('update-stock');


        });


    /*
    |--------------------------------------------------------------------------
    | Stock Transfers
    |--------------------------------------------------------------------------
    */

    Route::prefix('transfers')
        ->name('transfers.')
        ->controller(TransfersController::class)
        ->group(function () {

            Route::get('/', 'index')->name('index');

            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');

        });


    /*
    |--------------------------------------------------------------------------
    | Branch Management
    |--------------------------------------------------------------------------
    */

    Route::prefix('branches')
        ->name('branches.')
        ->controller(BranchesController::class)
        ->group(function () {

            Route::get('/', 'index')->name('index');

            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');

            Route::get('/{branch}', 'show')->name('show');

            Route::get('/{branch}/edit', 'edit')->name('edit');
            Route::put('/{branch}', 'update')->name('update');

            // Route::get('/{branch}/users/{user}/edit', 'editUser')->name('users.edit');
            // Route::put('/{branch}/users/{user}', 'updateUser')->name('users.update');

            Route::delete('/{branch}', 'destroy')->name('destroy'); 

        });


    /*
    |--------------------------------------------------------------------------
    | Store Management
    |--------------------------------------------------------------------------
    */

    Route::prefix('stores')
        ->name('stores.')
        ->controller(StoresController::class)
        ->group(function () {

            Route::get('/', 'index')->name('index');

            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');

            Route::get('/{store}', 'show')->name('show');

            Route::get('/{store}/edit', 'edit')->name('edit');
            Route::put('/{store}', 'update')->name('update');

            Route::delete('/{store}', 'destroy')->name('destroy');

        });


    /*
    |--------------------------------------------------------------------------
    | Product Management
    |--------------------------------------------------------------------------
    */

    Route::prefix('products')
        ->name('products.')
        ->controller(ProductsController::class)
        ->group(function () {

            Route::get('/', 'index')->name('index');

            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');

            Route::get('/{product}', 'show')->name('show');

            Route::get('/{product}/edit', 'edit')->name('edit');
            Route::put('/{product}', 'update')->name('update');

            Route::delete('/{product}', 'destroy')->name('destroy');

        });


    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    Route::prefix('reports')
    ->name('reports.')
    ->controller(ReportsController::class)
    ->group(function () {

        Route::get('/', 'index')
            ->name('index');

        Route::get('/sales', 'sales')
            ->name('sales');

        Route::get('/inventory', 'inventory')
            ->name('inventory');

        Route::get('/stock-movements', 'stockMovements')
            ->name('stock-movements');

        Route::get('/transfers', 'transfers')
            ->name('transfers');

        Route::get('/products', 'products')
            ->name('products');

        Route::get('/branches', 'branches')
            ->name('branches');

        Route::get('/stores', 'stores')
            ->name('stores');
    });

});