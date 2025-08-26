<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtTokenVerify;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;


// Backend

Route::group(['prefix' => 'backend'], function (): void {

    Route::post('register', [RegisterController::class, 'register']);
    Route::post('login', [LoginController::class, 'login']);
    Route::post('password/reset/send/otp', [ResetPasswordController::class, 'sendOtp']);
    Route::post('password/reset/verify/otp', [ResetPasswordController::class, 'verifyOtp']);
    Route::post('password/reset', [ResetPasswordController::class, 'resetPassword']);

    Route::group(['middleware' => JwtTokenVerify::class], function () {
        Route::get('profile', [ProfileController::class, 'profile']);
        Route::post('profile-update', [ProfileController::class, 'profileUpdate']);
        Route::post('logout', [LogoutController::class, 'logout']);
    });

    Route::group(['prefix' => 'admin/categories'], function () {
        Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
        // web.php or api.php
        Route::get('/json', [CategoryController::class, 'getAllCategories'])->name('categories.json');

        Route::get('/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/store', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/edit/{category}', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/update/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/delete/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    })->middleware([JwtTokenVerify::class, 'role:admin']);;




    Route::group(['prefix' => 'admin/products'], function () {
        Route::get('/list', [ProductController::class, 'adminProductList'])->name('admin.products.adminProductList');
        Route::get('/create', [ProductController::class, 'adminProductCreate'])->name('admin.products.adminProductCreate');
        Route::post('/store', [ProductController::class, 'store'])->name('admin.products.store');
        Route::get('/show/{product}', [ProductController::class, 'adminProductShow'])->name('admin.products.adminProductShow');
        Route::get('/edit/{product}', [ProductController::class, 'adminProductEdit'])->name('admin.products.adminProductEdit');
        Route::put('/update/{product_id}', [ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/delete/{product}', [ProductController::class, 'adminProductDelete'])->name('admin.products.adminProductDelete');
    })->middleware([JwtTokenVerify::class, 'role:admin']);

    Route::group(['prefix' => 'products'], function () {
        Route::get('/', [ProductController::class, 'index']);
        // Route::post('store', [ProductController::class, 'store']);
        // Route::put('update/{product}', [ProductController::class, 'update']);
        Route::put('{product}', [ProductController::class, 'show']);
    })->middleware([JwtTokenVerify::class, 'role:customer,admin']);

    Route::group(['prefix' => 'invoices'], function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::post('store', [InvoiceController::class, 'store']);

        Route::get('show/{invoice}', [InvoiceController::class, 'show'])->name('admin.invoice.show');
        Route::get('print/{invoice}', [InvoiceController::class, 'print']);
    })->middleware(JwtTokenVerify::class);
});



Route::prefix('admin')->middleware([JwtTokenVerify::class, 'role:admin'])->group(function () {

    // Sales
    Route::post('/sales/create', [SalesController::class, 'createInvoice'])->name('sales.create');
    Route::get('/sales/list', [SalesController::class, 'listInvoices'])->name('sales.list');

    // Stock
    Route::get('/stock/report', [StockController::class, 'stockReport'])->name('stock.report');

    // Invoice
    Route::get('/invoices/print/{id}', [InvoiceController::class, 'printInvoice'])->name('invoices.print');
    Route::get('/invoices/list', [InvoiceController::class, 'listInvoices'])->name('invoices.list');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // Orders
    Route::post('/orders/create', [OrderController::class, 'createOrder'])->name('orders.create');
    Route::get('/orders/list', [OrderController::class, 'listOrders'])->name('orders.list');
});


//frontend Routes

Route::get('/', [PageController::class, 'index']);

Route::get('/register', [PageController::class, 'registration'])->name('register');
Route::get('/login', [PageController::class, 'login'])->name('login');
Route::get('/reset-password', [PageController::class, 'resetPassword'])->name('reset-password');
Route::get('/send-otp', [PageController::class, 'sendOtp'])->name('forgot-password.send-otp');
Route::get('/verify-otp', [PageController::class, 'verifyOtp'])->name('forgot-password.verify-otp');


Route::group(['middleware' => JwtTokenVerify::class], function () {
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [PageController::class, 'profile'])->name('profile');
    Route::get('/customer/products', [ProductController::class, 'customerProducts'])->name('customer.products');
    Route::post('/customer/product/order', [OrderController::class, 'customerOrderStore'])->name('customer.product.store');
    Route::get('/customer/orders/list', [OrderController::class, 'customerOrders'])->name('customer.order.list');
    //customer order list for admin
    Route::get('/customer/orders', [OrderController::class, 'adminCustomerOrders'])->name('admin.customer.orders');
});
