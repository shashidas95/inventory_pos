<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosController;
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
use App\Http\Controllers\StoreUserController;
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

    Route::prefix('store-users')->group(function () {

        // Manage store users page
        Route::get('/', [StoreUserController::class, 'index'])->name('store.users.index');

        // Assign user to store
        Route::post('/assign', [StoreUserController::class, 'assign'])->name('store.users.assign');

        // Remove user from store
        Route::delete('/{store}/{user}', [StoreUserController::class, 'remove'])->name('store.users.remove');
    });

    // Admin Customer routes
    Route::prefix('customers')->group(function () {
        Route::get('/', action: [CustomerController::class, 'index'])->name('admin.customers.index'); // blade view
        Route::get('/create', [CustomerController::class, 'create'])->name('admin.customers.create');
        Route::get('/list', [CustomerController::class, 'list'])->name('admin.customers.list'); // ajax list
        Route::post('/store', [CustomerController::class, 'store'])->name('admin.customers.store'); // ajax store
    });

    // -------------------- Categories --------------------
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::get('/json', [CategoryController::class, 'getAllCategories']);
        Route::post('/store', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/edit/{category}', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::post('/update/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::post('/delete/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });


    // -------------------- Products --------------------
    Route::prefix('products')->group(function () {
        Route::get('/list', [ProductController::class, 'adminProductList'])->name('admin.products.adminProductList');
        Route::get('/create', [ProductController::class, 'adminProductCreate'])->name('admin.products.adminProductCreate');
        Route::get('/edit/{product}', [ProductController::class, 'adminProductEdit'])->name('admin.products.adminProductEdit');
        Route::get('/show/{product}', [ProductController::class, 'adminProductShow'])->name('admin.products.adminProductShow');

        Route::post('/store', [ProductController::class, 'store'])->name('admin.products.store');
        Route::put('/update/{product}', [ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/delete/{product}', [ProductController::class, 'adminProductDelete'])->name('admin.products.adminProductDelete');
    });

    // -------------------- Orders --------------------
    Route::prefix('orders')->group(function () {
        Route::get('/list', [OrderController::class, 'listOrders'])->name('orders.list');
        Route::post('/create', [OrderController::class, 'store'])->name('orders.store');   // renamed createOrder -> store
        Route::post('/update/{id}', [OrderController::class, 'update'])->name('orders.update');
        Route::post('/delete/{id}', [OrderController::class, 'destroy'])->name('orders.delete');
        // Create invoice for an order
        Route::post('/{order}/invoice', [InvoiceController::class, 'createInvoice'])
            ->name('orders.createInvoice');
    });
    // -------------------- Sales --------------------
    Route::prefix('sales')->group(function () {
        // Page
        Route::get('/create', [PageController::class, 'salesPage'])->name('sales.page');
    });
    // -------------------- Invoices --------------------
    Route::prefix('invoices')->group(function () {
        Route::get('/list', [InvoiceController::class, 'listInvoices'])->name('invoices.list');
        Route::get('/show/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/print/{id}', [InvoiceController::class, 'printInvoice'])->name('invoices.print');
        Route::post('/store-sale', [InvoiceController::class, 'storeSale'])->name('invoice.store.sale');
    });



    // -------------------- Stock --------------------
    Route::prefix('stock')->group(function () {
        Route::get('/', [PageController::class, 'stockPage'])->name('stock.page');
        Route::get('/report', [StockController::class, 'stockReport'])->name('stock.report');

        Route::post('/create', [StockController::class, 'store'])->name('stock.create');
        Route::post('/update/{id}', [StockController::class, 'update'])->name('stock.update');
        Route::post('/delete/{id}', [StockController::class, 'destroy'])->name('stock.destroy');
    });
    Route::get('/api/sales-stats', [DashboardController::class, 'salesStats'])->name('api.sales.stats');
    Route::get('/invoice/{id}/print', [PosController::class, 'printReceipt']);
    Route::post('/checkout', [PosController::class, 'checkout']);
});






// -------------------- ADMIN UI PAGES (Blade) --------------------
Route::prefix('admin')->middleware([JwtTokenVerify::class, 'role:admin,manager'])->group(function () {

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

// -------------------- SHARED/AUTHED API STATS (Manager/Customer/Admin) --------------------
Route::middleware(JwtTokenVerify::class)->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    // Customer Pages
    // Route::get('/dashboard', [PageController::class, 'dashboard'])->name('customer.dashboard');
    Route::get('/profile', [PageController::class, 'profile'])->name('profile');
    Route::prefix('customer')->name('customer.')->group(function () {
        // Product browsing / POS
        Route::get('/products', [ProductController::class, 'customerProducts'])->name('products');
        Route::get('/create-order', [OrderController::class, 'customerCreateOrderPage'])->name('create.order.page');
        Route::post('/order/store', [OrderController::class, 'customerOrderStore'])->name('order.store');

        // Customer order listing
        Route::get('/order/list', [OrderController::class, 'customerOrders'])->name('order.list');

        // Customer invoices
        Route::get('/invoices', [InvoiceController::class, 'customerInvoices'])->name('invoices');
    }); // API stats (optional for dashboard)
    Route::get('api/orders/stats', [OrderController::class, 'getOrderStats'])->name('api.orders.stats');
    Route::get('api/invoices/stats', [InvoiceController::class, 'getInvoiceStats'])->name('api.invoices.stats');
});
Route::get('/list-customer', [CustomerController::class, 'list'])->name('customer.list');
Route::get('/list-product', [ProductController::class, 'index'])->name('product.list');




// MANAGER-SPECIFIC ROUTES (ROLE: MANAGER ONLY)
// These routes call methods that apply store_id filtering.
// --------------------------------------------------------------------------
Route::prefix('manager')->middleware([JwtTokenVerify::class, 'role:manager'])->group(function () {

    // Dashboard (if needed, but usually redirect to index)
    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('manager.dashboard');

    // PRODUCTS
    Route::get('/products', [ProductController::class, 'storeScopedIndex'])->name('manager.products.list');
    Route::get('/products/assign', [ProductController::class, 'assignIndex'])->name('manager.products.assignIndex');
    Route::post('/products/assign', [ProductController::class, 'assignProducts'])->name('manager.products.assign');

    // CATEGORIES
    Route::get('/categories', [CategoryController::class, 'storeScopedIndex'])->name('manager.categories.list');

    // INVOICES
    Route::get('/invoices', [InvoiceController::class, 'storeScopedIndex'])->name('manager.invoices.list');

    // ORDERS
    Route::get('/orders', [OrderController::class, 'storeScopedIndex'])->name('manager.orders.list');

    // ... add any other necessary store-scoped routes here ...
});
