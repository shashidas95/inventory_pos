<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            // Admin / Super Admin view
            $productsCount = Product::count();
            $categoriesCount = Category::count();
            $customersCount = User::where('role', 'customer')->count();
            $ordersCount = Order::count();
            $invoicesCount = Invoice::count();

            $totalSales = Invoice::sum('final_total');

            $todaySales = Invoice::whereDate('created_at', Carbon::today())
                ->sum('final_total');

            $monthSales = Invoice::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('final_total');

            $recentOrders = Order::latest()->take(5)->get();
            $recentInvoices = Invoice::latest()->take(5)->get();
        } elseif ($user->role === 'manager') {
            // Manager: only their store
            $storeId = $user->store_id;

            $productsCount = Product::whereHas('stores', fn($q) => $q->where('store_id', $storeId))->count();
            $categoriesCount = Category::count(); // optional: categories can be global or store-specific
            $customersCount = User::where('role', 'customer')->where('store_id', $storeId)->count();
            $ordersCount = Order::where('store_id', $storeId)->count();
            $invoicesCount = Invoice::where('store_id', $storeId)->count();

            $totalSales = Invoice::where('store_id', $storeId)->sum('final_total');

            $todaySales = Invoice::where('store_id', $storeId)
                ->whereDate('created_at', Carbon::today())
                ->sum('final_total');

            $monthSales = Invoice::where('store_id', $storeId)
                ->whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('final_total');

            $recentOrders = Order::where('store_id', $storeId)->latest()->take(5)->get();
            $recentInvoices = Invoice::where('store_id', $storeId)->latest()->take(5)->get();
        } else {
            // Customer view
            $totalOrders = Order::where('user_id', $user->id)->count();
            $totalInvoices = Invoice::where('customer_id', $user->id)->count();
            $totalSpent = Invoice::where('customer_id', $user->id)->sum('final_total');

            $recentOrders = Order::where('user_id', $user->id)->latest()->take(5)->get();
            $recentInvoices = Invoice::where('customer_id', $user->id)->latest()->take(5)->get();
        }

        return view('pages.dashboard.dashboard-page', compact(
            'user',
            'productsCount',
            'categoriesCount',
            'customersCount',
            'ordersCount',
            'invoicesCount',
            'totalSales',
            'todaySales',
            'monthSales',
            'recentOrders',
            'recentInvoices'
        ));
    }

    public function salesStats()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $todaySales = Invoice::whereDate('created_at', Carbon::today())->sum('final_total');
            $monthSales = Invoice::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('final_total');
            $totalSales = Invoice::sum('final_total');
        } elseif ($user->role === 'manager') {
            $storeId = $user->store_id;

            $todaySales = Invoice::where('store_id', $storeId)
                ->whereDate('created_at', Carbon::today())
                ->sum('final_total');

            $monthSales = Invoice::where('store_id', $storeId)
                ->whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('final_total');

            $totalSales = Invoice::where('store_id', $storeId)->sum('final_total');
        } else {
            $todaySales = $monthSales = $totalSales = 0; // customers don’t see sales stats
        }

        return response()->json([
            'todaySales' => $todaySales,
            'monthSales' => $monthSales,
            'totalSales' => $totalSales,
        ]);
    }
}
