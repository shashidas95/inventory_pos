<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Admin stats
        if ($user->role === 'admin') {
            $productsCount = \App\Models\Product::count();
            $categoriesCount = \App\Models\Category::count();
            $customersCount = \App\Models\User::where('role', 'customer')->count();
            $ordersCount = \App\Models\Order::count();
            $invoicesCount = \App\Models\Invoice::count();
            $totalSales = \App\Models\Invoice::sum('total_amount');
            // $vat = \App\Models\Invoice::sum('vat') ?? 0; // if you store VAT
            // $payable = \App\Models\Invoice::sum('total_amount') - \App\Models\Invoice::sum('paid_amount') ?? 0; // example

            $recentOrders = \App\Models\Order::latest()->take(5)->get();
            $recentInvoices = \App\Models\Invoice::latest()->take(5)->get();

            return view('pages.dashboard.dashboard-page', compact(
                'user',
                'productsCount',
                'categoriesCount',
                'customersCount',
                'ordersCount',
                'invoicesCount',
                'totalSales',
                // 'vat',
                // 'payable',
                'recentOrders',
                'recentInvoices'
            ));
        }
        // Customer stats
        else {
            $totalOrders = \App\Models\Order::where('user_id', $user->id)->count();
            $totalInvoices = \App\Models\Invoice::where('user_id', $user->id)->count();
            $totalSpent = \App\Models\Invoice::where('user_id', $user->id)->sum('total_amount');

            $orders = \App\Models\Order::where('user_id', $user->id)->latest()->take(5)->get();
            $invoices = \App\Models\Invoice::where('user_id', $user->id)->latest()->take(5)->get();

            return view('pages.dashboard.dashboard-page', compact(
                'user',
                'totalOrders',
                'totalInvoices',
                'totalSpent',
                'orders',
                'invoices'
            ));
        }
    }
}
