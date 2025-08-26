<?php

namespace App\Http\Controllers;

// use Storage;
use Exception;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::orderBy('name', 'asc')->get();
        if ($products->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'data' => $products,
                'message' => 'Product found.'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'data' => '',
                'message' => 'Product not found'
            ]);
        }
    }
    public function adminProductCreate(Request $request)
    {
        $categories = Category::all();

        return view('pages.dashboard.products.product-page', compact('categories'));
    }
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'quantity' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation error!'
            ]);
        }

        $productData = $request->except('image');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
        }
        $productData['image'] = $path;
        $product = Product::create($productData);
        return response()->json([
            'status' => 'success',
            'data'   => $product,
            'message' => 'Product created successfully'
        ]);
    }

    public function adminProductEdit(Product $product)
    {
        $categories = Category::all();
        return view('pages.dashboard.products.product-edit', compact('categories', 'product'));
    }


    public function show(Product $product): JsonResponse
    {

        try {
            if (!empty($product)) {
                return response()->json([
                    'status' => 'success',
                    'data' => $product,
                    'message' => 'Product found.'
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'data' => '',
                'message' => 'Product not found'
            ]);
        }
    }
    public function adminProductShow($product_id)
    {
        $product = Product::findOrFail($product_id);
        return response()->json([
            'status' => 'success',
            'data' => $product
        ]);
    }
    public function update($product_id, Request $request): JsonResponse | RedirectResponse
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric',
            'quantity'    => 'required|integer',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'errors'  => $validator->errors(),
                'message' => 'Validation error!',
            ], 422); // 422 is standard for validation errors
        }


        $path = '';
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
        }
        $product = Product::find($product_id);

        // Update fields
        $product->category_id = $request->category_id;
        $product->name        = $request->name;
        $product->description = $request->description;
        $product->price       = $request->price;
        $product->quantity    = $request->quantity;

        // Only update image if a new one is uploaded
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = $path;
        }

        $product->save();

        return response()->json([
            'status'  => 'success',
            'data'    => $product,
            'message' => 'Product updated successfully.'
        ]);
    }

    public function adminProductList()
    {
        $products = Product::orderBy('name', 'asc')->get();

        return view('pages.dashboard.admin.products.list', compact('products'));
    }

    public function adminProductDelete(Product $product)
    {
        // Delete image from storage if exists
        if ($product->image && Storage::exists($product->image)) {
            Storage::delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.adminProductList')->with('success', 'Product deleted successfully.');
    }

    public function customerProducts()
    {
        $products = Product::orderBy('name', 'asc')->get();

        return view('pages.dashboard.customers.products.list', compact('products'));
    }
}
