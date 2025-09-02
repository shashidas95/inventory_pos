<?php

namespace App\Http\Controllers\Web;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PageController extends Controller
{
    // Frontend Pages
    public function index()
    {
        return view('pages.home');
    }

    public function login()
    {
        return view('pages.auth.login');
    }

    public function registration()
    {
        return view('pages.auth.register');
    }

    public function sendOtp()
    {
        return view('pages.auth.send-otp-page');
    }

    public function verifyOtp(Request $request)
    {
        return view('pages.auth.verify-otp-page');
    }

    public function resetPassword(Request $request)
    {
        return view('pages.auth.reset-pass-page');
    }

    // Authenticated customer pages
    public function dashboard(Request $request)
    {
        return view('pages.dashboard.dashboard-page');
    }

    public function profile(Request $request)
    {
        return view('pages.dashboard.profile-page');
    }

    // Admin UI Pages (Blade)
    public function salesPage()
    {

        $products = Product::all(); // fetch all products

        return view('pages.dashboard.admin.sales.sale-page', compact('products'));
    }

    public function stockPage()
    {
        return view('pages.dashboard.admin.stock.stock-page');
    }

    public function invoicePage()
    {
        return view('pages.dashboard.admin.invoices.invoice-page');
    }

    public function ordersPage()
    {
        return view('pages.dashboard.admin.orders.orders-page');
    }

    // Products Page (for creating new product)
    public function adminProductCreate(Request $request)
    {
        $categories = Category::all();
        return view('pages.dashboard.products.product-page', compact('categories'));
    }

    // Optional: other pages if needed
    public function reports()
    {
        return view('pages.dashboard.admin.reports.report-page');
    }

    public function customers()
    {
        return view('pages.dashboard.customers.customer-page');
    }
}
