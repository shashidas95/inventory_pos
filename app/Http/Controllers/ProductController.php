<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // Admin: List all products
    public function adminProductList()
    {
        $products = Product::with('stores')->orderBy('name', 'asc')->get();
        return view('pages.dashboard.admin.products.list', compact('products'));
    }

    // Admin: Show product creation form
    public function adminProductCreate()
    {
        $categories = Category::all();
        $stores = Store::all();
        return view('pages.dashboard.products.product-page', compact('categories', 'stores'));
    }

    // Admin: Store new product with store stock
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
            'stores' => 'required|array|min:1',
            'stores.*.store_id' => 'required|exists:stores,id',
            'stores.*.quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation error!'
            ], 422);
        }

        // Handle image
        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
        }

        // Create product with quantity = 0 (multi-store stock is in pivot table)
        $product = Product::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $path,
            'quantity' => 0, // avoid SQL error
        ]);

        // Attach stores with quantities
        foreach ($request->stores as $store) {
            $product->stores()->attach($store['store_id'], ['quantity' => $store['quantity']]);
        }

        return response()->json([
            'status' => true,
            'data' => $product,
            'message' => 'Product created successfully with store stock!'
        ], 201);
    }


    // Admin: Show edit form
    public function adminProductEdit(Product $product)
    {
        $categories = Category::all();
        $stores = Store::all();
        $product->load('stores');
        return view('pages.dashboard.products.product-edit', compact('categories', 'stores', 'product'));
    }

    // Admin: Update product & store stock
    public function update(Product $product, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
            'stores' => 'required|array|min:1', // now stores is like ['1' => 10, '2' => 0]
            'stores.*' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation error!'
            ], 422);
        }

        // Update image if uploaded
        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->update($request->only(['category_id', 'name', 'description', 'price']));

        // Sync stores with quantities
        $syncData = [];
        foreach ($request->stores as $storeId => $quantity) {
            $syncData[$storeId] = ['quantity' => (int)$quantity];
        }
        $product->stores()->sync($syncData);

        return response()->json([
            'status' => 'success',
            'data' => $product->load('stores'),
            'message' => 'Product updated successfully.'
        ]);
    }

    // Admin: Delete product
    public function adminProductDelete(Product $product)
    {
        if ($product->image && Storage::exists($product->image)) {
            Storage::delete($product->image);
        }
        $product->stores()->detach();
        $product->delete();

        return redirect()->route('admin.products.adminProductList')->with('success', 'Product deleted successfully.');
    }

    // Customer: List products for a specific store
    public function customerProducts($storeId = null)
    {
        $query = Product::query()->with(['stores' => function ($q) use ($storeId) {
            if ($storeId) $q->where('store_id', $storeId);
        }]);

        if ($storeId) {
            $query->whereHas('stores', function ($q) use ($storeId) {
                $q->where('store_id', $storeId)->where('quantity', '>', 0);
            });
        }

        $products = $query->orderBy('name', 'asc')->get();

        return view('pages.dashboard.customers.products.list', compact('products', 'storeId'));
    }

    // Public API: Get products JSON
    public function index(): JsonResponse
    {
        $products = Product::with('stores')->orderBy('name', 'asc')->get();
        return response()->json([
            'status' => $products->isEmpty() ? 'error' : 'success',
            'data' => $products,
            'message' => $products->isEmpty() ? 'No products found' : 'Products fetched successfully'
        ]);
    }
    public function storeIndex()
    {
        $user = auth()->user();

        // 1. Role Check: Ensure the authenticated user is actually a manager.
        if ($user->role !== 'manager') {
            // Log unauthorized access or redirect to an error page
            // Since middleware should block this, this is a safety net.
            abort(403, 'Unauthorized access.');
        }

        $storeId = $user->store_id;

        if (!$storeId) {
            // Handle manager not assigned to any store
            return view('manager.products.list')->with('products', collect([]));
        }

        // Fetch products associated with the manager's store via the pivot table
        $products = Product::whereHas('stores', function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        })->paginate(20);

        return view('manager.products.list', compact('products'));
    }
}
