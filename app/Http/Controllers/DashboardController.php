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
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /**
     * Main entry point for the dashboard, determining role and fetching data.
     */
    public function index()
    {
        $user = auth()->user();

        // 1. Determine role and fetch the necessary data via private methods
        if ($user->isAdmin()) {
            $data = $this->getAdminData();
        } elseif ($user->role === 'manager') {
            $data = $this->getManagerData($user->store_id);
        } else {
            $data = $this->getCustomerData($user->id);
        }

        // 2. Add the user object and return the view
        $data['user'] = $user;

        // Ensure all variables expected by the view's compact() are present in $data.
        // The helper methods ensure this is the case.
        return view('pages.dashboard.dashboard-page', $data);
    }

    /**
     * API endpoint to return dynamic sales statistics (Today, Month, Total).
     */
    public function salesStats()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            // Admin: Global stats
            $data = $this->calculateSalesStats();
        } elseif ($user->role === 'manager') {
            // Manager: Store-scoped stats
            $data = $this->calculateSalesStats($user->store_id);
        } else {
            // Customer: No sales stats
            $data = ['todaySales' => 0, 'monthSales' => 0, 'totalSales' => 0];
        }

        return response()->json($data);
    }

    // --------------------------------------------------------------------------
    // PRIVATE HELPER METHODS FOR DATA FETCHING
    // --------------------------------------------------------------------------

    /**
     * Fetches all global statistics for the Admin role.
     * @return array
     */
    private function getAdminData(): array
    {
        $productsCount = Product::count();
        $categoriesCount = Category::count();
        $customersCount = User::where('role', 'customer')->count();
        $ordersCount = Order::count();
        $invoicesCount = Invoice::count();

        $salesStats = $this->calculateSalesStats();

        return [
            'productsCount' => $productsCount,
            'categoriesCount' => $categoriesCount,
            'customersCount' => $customersCount,
            'ordersCount' => $ordersCount,
            'invoicesCount' => $invoicesCount,

            'totalSales' => $salesStats['totalSales'],
            'todaySales' => $salesStats['todaySales'],
            'monthSales' => $salesStats['monthSales'],

            'recentOrders' => Order::latest()->take(5)->get(),
            'recentInvoices' => Invoice::latest()->take(5)->get(),

            // Unused customer variables must still be defined for the view
            'totalOrders' => 0,
            'totalInvoices' => 0,
            'totalSpent' => 0,
        ];
    }

    /**
     * Fetches all store-scoped statistics for the Manager role.
     * @param int $storeId
     * @return array
     */
    private function getManagerData(int $storeId): array
    {
        // 1. Core Counts (scoped)
        $productsCount = Product::whereHas('stores', fn($q) => $q->where('store_id', $storeId))->count();
        $categoriesCount = Category::count(); // Global categories assumption
        $customersCount = User::where('role', 'customer')->where('store_id', $storeId)->count();
        $ordersCount = Order::where('store_id', $storeId)->count();
        $invoicesCount = Invoice::where('store_id', $storeId)->count();

        // 2. Sales Stats (scoped, reused helper)
        $salesStats = $this->calculateSalesStats($storeId);

        return [
            'productsCount' => $productsCount,
            'categoriesCount' => $categoriesCount,
            'customersCount' => $customersCount,
            'ordersCount' => $ordersCount,
            'invoicesCount' => $invoicesCount,

            'totalSales' => $salesStats['totalSales'],
            'todaySales' => $salesStats['todaySales'],
            'monthSales' => $salesStats['monthSales'],

            // Recent activity (scoped)
            'recentOrders' => Order::where('store_id', $storeId)->latest()->take(5)->get(),
            'recentInvoices' => Invoice::where('store_id', $storeId)->latest()->take(5)->get(),

            // Unused customer variables must still be defined for the view
            'totalOrders' => 0,
            'totalInvoices' => 0,
            'totalSpent' => 0,
        ];
    }

    /**
     * Fetches personal statistics for the Customer role.
     * @param int $userId
     * @return array
     */
    private function getCustomerData(int $userId): array
    {
        return [
            // Customer-specific totals
            'totalOrders' => Order::where('user_id', $userId)->count(),
            'totalInvoices' => Invoice::where('customer_id', $userId)->count(),
            'totalSpent' => Invoice::where('customer_id', $userId)->sum('final_total'),

            // Recent activity (scoped)
            'recentOrders' => Order::where('user_id', $userId)->latest()->take(5)->get(),
            'recentInvoices' => Invoice::where('customer_id', $userId)->latest()->take(5)->get(),

            // Unused Admin/Manager variables must still be defined for the view
            'productsCount' => 0,
            'categoriesCount' => 0,
            'customersCount' => 0,
            'ordersCount' => 0,
            'invoicesCount' => 0,
            'totalSales' => 0,
            'todaySales' => 0,
            'monthSales' => 0,
        ];
    }

    /**
     * Calculates sales stats, optionally scoped by store ID.
     * This avoids code duplication in both index() and salesStats().
     * @param int|null $storeId The store ID to scope the query to, or null for global.
     * @return array
     */
    private function calculateSalesStats(?int $storeId = null): array
    {
        $query = Invoice::query();

        // Apply store scoping if storeId is provided (Manager)
        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        // Calculate totals using the scoped query
        $totalSales = $query->sum('final_total');

        $todaySales = (clone $query)
            ->whereDate('created_at', Carbon::today())
            ->sum('final_total');

        $monthSales = (clone $query)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('final_total');

        return [
            'todaySales' => $todaySales,
            'monthSales' => $monthSales,
            'totalSales' => $totalSales,
        ];
    }
}
