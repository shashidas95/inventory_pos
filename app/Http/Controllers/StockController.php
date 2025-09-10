<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Show stock report for all stores or a specific store
     */
    public function stockReport(Request $request)
    {
        $user = auth()->user();
        $storeId = $request->store_id ?? null;
        $store = null;

        if ($user->role === 'manager') {
            // Managers: force store_id to their own store
            $storeId = $user->store_id;
        }

        if ($storeId) {
            // 🔹 Single store report
            $store = Store::with(['products' => function ($q) {
                $q->withPivot('quantity');
            }])->findOrFail($storeId);

            $products = $store->products;
            $stores = ($user->isAdmin()) ? Store::all() : [$store]; // dropdown only shows their store for managers
        } else {
            // 🔹 All stores report (admin only)
            if ($user->isAdmin()) {
                $products = Product::with('stores')->get();
                $stores = Store::with(['products' => function ($q) {
                    $q->withPivot('quantity');
                }])->get();
            } else {
                // Non-admin without store_id should not see all stores
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to view all stores stock.'
                ], 403);
            }
        }

        return view(
            'components.dashboard.admin.stock.report',
            compact('products', 'store', 'storeId', 'stores')
        );
    }
}
