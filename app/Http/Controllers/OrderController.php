<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // Admin: Create order for a customer (single or multi-product)
    public function createOrder(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $order = Order::create([
            'user_id' => $request->user_id,
            'status' => 'pending',
            'total' => 0, // will calculate below
        ]);

        $totalAmount = 0;
        foreach ($request->products as $item) {
            $product = Product::findOrFail($item['product_id']);
            $lineTotal = $product->price * $item['quantity'];
            $totalAmount += $lineTotal;

            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);
        }

        $order->update(['total' => $totalAmount]);

        return response()->json(['status' => 'success', 'order' => $order->load('details.product')]);
    }

    // Customer: Create multi-product POS order
    // Customer: create multi-product POS order
    public function customerOrderStore(Request $request)
    {

        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty' => 'required|integer|min:1',
            'vat' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
        ]);

        $userId = Auth::id();
        $vat = $request->vat ?? 0;
        $discount = $request->discount ?? 0;

        $total = 0;
        $orderDetails = [];

        foreach ($request->products as $p) {
            $product = Product::findOrFail($p['product_id']);
            $amount = $product->price * $p['qty'];
            $total += $amount;

            $orderDetails[] = [
                'product_id' => $product->id,
                'quantity' => $p['qty'],
                'price' => $product->price
            ];
        }

        $totalWithVatDiscount = $total + ($total * $vat / 100) - $discount;

        $order = Order::create([
            'user_id' => $userId,
            'status' => 'pending',
            'total' => $totalWithVatDiscount
        ]);

        foreach ($orderDetails as $detail) {
            $detail['order_id'] = $order->id;
            OrderDetail::create($detail);
        }

        return response()->json(['status' => 'success', 'data' => $order]);
    }
    // Admin: List all orders
    public function listOrders()
    {
        $orders = Order::with('user', 'details.product')->latest()->get();
        return view('pages.dashboard.admin.orders.index', compact('orders'));
    }

    // Customer: List own orders (JSON for dashboard)
    public function customerOrders()
    {
        $orders = Order::with('details.product')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('components.dashboard.customers.orders.order-list', compact('orders'));
    }

    // Admin: Update order
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update($request->only(['status', 'total']));
        return response()->json(['status' => 'success', 'data' => $order]);
    }

    // Admin: Delete order
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->details()->delete();
        $order->delete();
        return response()->json(['status' => 'success', 'message' => 'Order deleted']);
    }

    // Dashboard stats for Axios
    public function getOrderStats()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $totalOrders = Order::count();
            $totalProducts = Product::count();
            $totalCategories = Category::count();
            $totalCustomers = User::where('role', 'customer')->count();
        } else {
            $totalOrders = Order::where('user_id', $user->id)->count();
            $totalProducts = $totalCategories = $totalCustomers = 0;
        }

        return response()->json([
            'total_orders' => $totalOrders,
            'total_products' => $totalProducts,
            'total_categories' => $totalCategories,
            'total_customers' => $totalCustomers
        ]);
    }

    // Admin: List orders for all customers (Blade view)
    public function adminCustomerOrders()
    {
        $orders = Order::with('details.product', 'user')->latest()->get();
        return view('pages.dashboard.admin.orders.index', compact('orders'));
    }
}
