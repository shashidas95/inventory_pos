<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtTokenVerify;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;

// -------------------- BACKEND AUTH --------------------
Route::prefix('backend')->group(function () {

    // Auth
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('login', [LoginController::class, 'login']);
    Route::post('password/reset/send/otp', [ResetPasswordController::class, 'sendOtp']);
    Route::post('password/reset/verify/otp', [ResetPasswordController::class, 'verifyOtp']);
    Route::post('password/reset', [ResetPasswordController::class, 'resetPassword']);

    // Authenticated user
    Route::middleware(JwtTokenVerify::class)->group(function () {
        Route::get('profile', [ProfileController::class, 'profile']);
        Route::post('profile-update', [ProfileController::class, 'profileUpdate']);
        Route::post('logout', [LogoutController::class, 'logout']);
    });
});

// -------------------- BACKEND ADMIN APIs --------------------
Route::prefix('backend/admin')->middleware([JwtTokenVerify::class, 'role:admin'])->group(function () {

    // Categories
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::get('/json', [CategoryController::class, 'getAllCategories']);
        Route::post('/store', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/edit/{category}', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::post('/update/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::post('/delete/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });


    // Products
    Route::prefix('products')->group(function () {
        Route::get('/list', [ProductController::class, 'adminProductList'])->name('admin.products.adminProductList');
        Route::get('/create', [ProductController::class, 'adminProductCreate'])->name('admin.products.adminProductCreate');
        Route::get('/edit/{id}', [ProductController::class, 'adminProductEdit'])->name('admin.products.adminProductEdit');
        Route::get('/{id}', [ProductController::class, 'adminProductShow'])->name('admin.products.adminProductShow');

        Route::post('/store', [ProductController::class, 'store'])->name('admin.products.store');
        Route::post('/update/{id}', [ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/delete/{id}', [ProductController::class, 'adminProductDelete'])->name('admin.products.adminProductDelete');
    });


    // Sales
    Route::prefix('sales')->group(function () {
        Route::get('/create', [PageController::class, 'salesPage'])->name('sales.page'); // page for creating sale
        Route::get('/list', [SalesController::class, 'listInvoices'])->name('sales.list'); // list page
        Route::get('/{id}', [SalesController::class, 'show'])->name('sales.show'); // show sale details



        // Actions
        Route::post('/create', [SalesController::class, 'createInvoice'])->name('sales.create'); // create action
        Route::post('/update/{id}', [SalesController::class, 'update'])->name('sales.update'); // update action
        Route::post('/delete/{id}', [SalesController::class, 'destroy'])->name('sales.destroy'); // delete action

    });

    // Invoices
    Route::prefix('invoices')->group(function () {
        // Pages
        Route::get('/list', [InvoiceController::class, 'listInvoices'])->name('invoices.list'); // list page
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('invoices.show'); // show invoice details
        Route::get('/print/{id}', [InvoiceController::class, 'printInvoice'])->name('invoices.print'); // print invoice

        Route::post('/invoice-create', [SalesController::class, 'storeSale'])->name('invoice.create');

        // Actions
        Route::post('/create-sale', [InvoiceController::class, 'storeSale'])->name('invoice.store.sale');
        Route::post('/create', [InvoiceController::class, 'store'])->name('invoices.create');
        Route::post('/update/{id}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::post('/delete/{id}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    });
    // Route::prefix('backend/admin')->middleware(['jwtTokenVerify', 'role:admin'])->group(function () {

    //     // Create Sale Page
    //     Route::get('/sales/create', [PageController::class, 'salesPage'])->name('sales.page');

    //     // Store Sale & Invoice
    //     Route::post('/sales/create', [SalesController::class, 'createInvoice'])->name('sales.create');

    //     // Print Invoice
    //     Route::get('/invoices/print/{id}', [InvoiceController::class, 'printInvoice'])->name('invoices.print');
    // });



    // Stock
    Route::prefix('stock')->group(function () {
        // Pages
        Route::get('/', [PageController::class, 'stockPage'])->name('stock.page'); // stock page
        Route::get('/report', [StockController::class, 'stockReport'])->name('stock.report'); // stock report page

        // Actions
        Route::post('/create', [StockController::class, 'store'])->name('stock.create');
        Route::post('/update/{id}', [StockController::class, 'update'])->name('stock.update');
        Route::post('/delete/{id}', [StockController::class, 'destroy'])->name('stock.destroy');
    });

    // Orders
    Route::prefix('orders')->group(function () {
        // Pages / API
        Route::get('/list', [OrderController::class, 'listOrders'])->name('orders.list'); // orders list page
        Route::post('/create', [OrderController::class, 'createOrder'])->name('orders.create'); // create order
        Route::post('/update/{id}', [OrderController::class, 'update'])->name('orders.update');
        Route::post('/delete/{id}', [OrderController::class, 'destroy'])->name('orders.delete');
    });
});
Route::prefix('backend/admin')->middleware(['jwtTokenVerify', 'role:admin'])->group(function () {

    // Create Sale Page
    Route::get('/sales/create', [PageController::class, 'salesPage'])->name('sales.page');

    // Store Sale & Invoice
    Route::post('/sales/create', [SalesController::class, 'createInvoice'])->name('sales.create');

    // Print Invoice
    Route::get('/invoices/print/{id}', [InvoiceController::class, 'printInvoice'])->name('invoices.print');
});






// -------------------- ADMIN UI PAGES (Blade) --------------------
Route::prefix('admin')->middleware([JwtTokenVerify::class, 'role:admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name(name: 'admin.dashboard');
    // Route::get('/dashboard', [PageController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/dashboard/sales-summary', [DashboardController::class, 'dashboard'])->name('dashboard.sales-summary');


    // Sales Page
    Route::get('/sales', [PageController::class, 'salesPage'])->name('sales.page');

    // Stock Page
    Route::get('/stock', [PageController::class, 'stockPage'])->name('stock.page');

    // Invoices Page
    Route::get('/invoices', [PageController::class, 'invoicePage'])->name('invoices.page');

    // Orders Page
    Route::get('/orders', [PageController::class, 'ordersPage'])->name('orders.page');
});

// -------------------- FRONTEND PAGES --------------------
Route::get('/', [PageController::class, 'index']);
Route::get('/register', [PageController::class, 'registration'])->name('register');
Route::get('/login', [PageController::class, 'login'])->name('login');
Route::get('/reset-password', [PageController::class, 'resetPassword'])->name('reset-password');
Route::get('/send-otp', [PageController::class, 'sendOtp'])->name('forgot-password.send-otp');
Route::get('/verify-otp', [PageController::class, 'verifyOtp'])->name('forgot-password.verify-otp');

// Authenticated routes (customer & admin)
Route::middleware(JwtTokenVerify::class)->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    // Customer Pages
    // Route::get('/dashboard', [PageController::class, 'dashboard'])->name('customer.dashboard');
    Route::get('/profile', [PageController::class, 'profile'])->name('profile');
    Route::get('/customer/products', [ProductController::class, 'customerProducts'])->name('customer.products');
    Route::post('/customer/product/order', [OrderController::class, 'customerOrderStore'])->name('customer.product.store');
    Route::get('/customer/orders/list', [OrderController::class, 'customerOrders'])->name('customer.order.list');
    Route::get('/customer/orders', [OrderController::class, 'adminCustomerOrders'])->name('admin.customer.orders');

    // Dashboard API stats (for Blade + Axios)
    Route::get('/api/orders/stats', [OrderController::class, 'getOrderStats'])->name('api.order.stats');
    Route::get('/api/invoices/stats', [InvoiceController::class, 'getInvoiceStats'])->name('api.invoice.stats');
    Route::get('/api/invoices/stats', [DashboardController::class, 'invoiceStats'])->name('api.invoice.stats');
    Route::get('/api/orders/stats', [DashboardController::class, 'orderStats'])->name('api.order.stats');
});
Route::get('/list-customer', [CustomerController::class, 'list'])->name('customer.list');
Route::get('/list-product', [ProductController::class, 'index'])->name('product.list');
