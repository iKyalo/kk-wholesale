<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\StoresController;
use App\Http\Controllers\TransfersController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/sales', [SalesController::class, 'index'])
        ->name('sales.index');

    Route::get('/sales/create', [SalesController::class, 'create'])
        ->name('sales.create');

    Route::post('/sales', [SalesController::class, 'store'])
        ->name('sales.store');

    Route::get('/sales/by-store', [SalesController::class, 'byStore'])
        ->name('sales.by-store');

    Route::get('/sales/by-product', [SalesController::class, 'byProduct'])
        ->name('sales.by-product');

    Route::get('/sales/by-branch', [SalesController::class, 'byBranch'])
        ->name('sales.by-branch');

    Route::get('/inventory', [InventoryController::class, 'index'])
        ->name('inventory.index'); 

    Route::get('/inventory/by-store', [InventoryController::class, 'byStore'])
        ->name('inventory.by-store');

    Route::get('/inventory/edit-stock', [InventoryController::class, 'editStock'])
        ->name('inventory.edit-stock');

    Route::get('/transfers', [TransfersController::class, 'index'])
        ->name('transfers.index');

    Route::get('/transfers/create', [TransfersController::class, 'create'])
        ->name('transfers.create');

    Route::post('/transfers', [TransfersController::class, 'store'])
        ->name('transfers.store');


    Route::get('/branches', [BranchesController::class, 'index'])
        ->name('branches.index');  

    Route::get('/branches/create', [BranchesController::class, 'create'])
        ->name('branches.create');

    Route::post('/branches', [BranchesController::class, 'store'])
        ->name('branches.store');
        

    Route::get('/stores', [StoresController::class, 'index'])
        ->name('stores.index');

    Route::get('/stores/create', [StoresController::class, 'create'])
        ->name('stores.create');

    Route::post('/stores', [StoresController::class, 'store'])
        ->name('stores.store');

    Route::get('/products', [ProductsController::class, 'index'])
        ->name('products.index');
    Route::get('/products/create', [ProductsController::class, 'create'])
        ->name('products.create');
    Route::post('/products', [ProductsController::class, 'store'])
        ->name('products.store');
        
    Route::get('/reports', [ReportsController::class, 'index'])
        ->name('reports.index');
        
        
});