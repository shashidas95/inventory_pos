<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = Order::create([
            'user_id' => auth()->id(),
            'status' => 'pending',
            'total' => 0,
        ]);

        $total = 0;

        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $amount = $product->price * $item['quantity'];

            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);

            $total += $amount;
        }

        $order->total = $total;
        $order->save();

        return response()->json(['status' => 'success', 'data' => $order]);
    }

    public function listOrders()
    {
        $orders = Order::with('orderDetails.product')->latest()->get();
        return response()->json(['status' => 'success', 'data' => $orders]);
    }
}
