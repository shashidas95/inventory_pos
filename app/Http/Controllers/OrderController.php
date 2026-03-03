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
    // Admin / Manager: Create order for a customer
    public function createOrder(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'store_id' => $user->role === 'manager' ? '' : 'required|exists:stores,id', // manager store forced
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        // Force store_id if manager
        $storeId = $user->role === 'manager' ? $user->store_id : $request->store_id;

        $order = Order::create([
            'user_id' => $request->user_id,
            'store_id' => $storeId,
            'status' => 'pending',
            'total' => 0,
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
                'store_id' => $storeId,
            ]);
        }

        $order->update(['total' => $totalAmount]);

        return response()->json([
            'status' => 'success',
            'order' => $order->load('details.product')
        ]);
    }

    // Customer: Create POS order
    public function customerOrderStore(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'store_id' => $user->role === 'manager' ? '' : 'required|exists:stores,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty' => 'required|integer|min:1',
            'vat' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $userId = $user->id;
        $storeId = $user->role === 'manager' ? $user->store_id : $request->store_id;
        $vat = $request->vat ?? 0;
        $discount = $request->discount ?? 0;

        $subtotal = 0;
        $orderDetails = [];

        foreach ($request->products as $p) {
            $product = Product::findOrFail($p['product_id']);
            $lineTotal = $product->price * $p['qty'];
            $subtotal += $lineTotal;

            $orderDetails[] = [
                'product_id' => $product->id,
                'quantity' => $p['qty'],
                'price' => $product->price,
                'store_id' => $storeId,
            ];
        }

        $vatAmount = ($subtotal * $vat) / 100;
        $total = $subtotal + $vatAmount - $discount;

        $order = Order::create([
            'user_id' => $userId,
            'store_id' => $storeId,
            'status' => 'pending',
            'subtotal' => $subtotal,
            'vat' => $vatAmount,
            'discount' => $discount,
            'total' => $total
        ]);

        foreach ($orderDetails as $detail) {
            $detail['order_id'] = $order->id;
            OrderDetail::create($detail);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully!',
            'order' => $order->load('details.product')
        ]);
    }

    // Admin / Manager: List orders
    public function listOrders()
    {
        $user = auth()->user();
        $orders = Order::with('user', 'store', 'details.product')
            ->when($user->role === 'manager', fn($q) => $q->where('store_id', $user->store_id))
            ->latest()
            ->get();

        return view('pages.dashboard.admin.orders.index', compact('orders'));
    }

    // Customer: List own orders
    public function customerOrders()
    {
        $orders = Order::with('details.product', 'store')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('components.dashboard.customers.orders.order-list', compact('orders'));
    }

    // Admin / Manager: Update order
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
            'total' => 'nullable|numeric|min:0',
        ]);

        $order = Order::findOrFail($id);

        // Manager can only update orders from their store
        if ($user->role === 'manager' && $order->store_id !== $user->store_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to update this order.'
            ], 403);
        }

        $order->update($request->only(['status', 'total']));

        return response()->json([
            'status' => 'success',
            'data' => $order->load('details.product', 'store', 'user')
        ]);
    }

    // Admin / Manager: Delete order
    public function destroy($id)
    {
        $user = auth()->user();
        $order = Order::findOrFail($id);

        if ($user->role === 'manager' && $order->store_id !== $user->store_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to delete this order.'
            ], 403);
        }

        $order->details()->delete();
        $order->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Order deleted successfully'
        ]);
    }

    // Dashboard stats (store-aware)
    public function getOrderStats()
    {
        $user = auth()->user();

        if ($user->role === 'admin' || $user->role === 'super_admin') {
            $totalOrders = Order::count();
            $totalProducts = Product::count();
            $totalCategories = Category::count();
            $totalCustomers = User::where('role', 'customer')->count();
        } else { // manager
            $totalOrders = Order::where('store_id', $user->store_id)->count();
            $totalProducts = $totalCategories = $totalCustomers = 0;
        }

        return response()->json([
            'total_orders' => $totalOrders,
            'total_products' => $totalProducts,
            'total_categories' => $totalCategories,
            'total_customers' => $totalCustomers
        ]);
    }
    // Admin / Manager: Show single order
    public function show($id)
    {
        $user = auth()->user();

        $order = Order::with('details.product', 'store', 'user')->findOrFail($id);

        // Manager can only view orders from their store
        if ($user->role === 'manager' && $order->store_id !== $user->store_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to view this order.'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'order' => $order
        ]);
    }
    /**
     * Displays the list of Orders scoped to the current Manager's store.
     */
    public function storeScopedIndex()
    {
        $user = auth()->user();

        // 1. Role Check
        if ($user->role !== 'manager') {
            abort(403, 'Unauthorized access.');
        }

        $storeId = $user->store_id;

        if (!$storeId) {
            // Handle manager not assigned to any store
            return view('manager.orders.list')->with('orders', collect([]));
        }

        // Filter orders directly by the store_id column.
        $orders = Order::where('store_id', $storeId)->latest()->paginate(20);

        return view('manager.orders.list', compact('orders'));
    }
}
