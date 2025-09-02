<?php

// namespace App\Http\Controllers;

// use App\Models\Product;
// use Illuminate\Http\Request;

// class StockController extends Controller
// {
//     public function stockReport()
//     {
//         try {
//             $products = Product::all();
//             return response()->json(['status' => 'success', 'data' => $products]);
//         } catch (\Exception $e) {
//             return response()->json(['status' => 'error', 'message' => 'Something went wrong']);
//         }
//     }
// }

namespace App\Http\Controllers;

use App\Models\Product;

class StockController extends Controller
{
    public function stockReport()
    {
        $products = Product::all();
        return view('components.dashboard.admin.stock.report', compact('products'));
    }
}
