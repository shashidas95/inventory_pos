<?php
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthController;

Route::get('/products', function (Request $request) {
    // Get all products
    $products = DB::table('products')->get();
    // Filter stock < 10
    $productsLessThan10 = DB::table('products')->where('stock' ,'<', 10)->get();
    // Calculate SUM & AVG
    $sum = DB::table('products')->sum('price');
    $avg = DB::table('products')->avg('price');
    return response()->json([
        'all_products' => $products,
        'low_stock_products' => $productsLessThan10,
        'total_price' => $sum,
        'average_price' => $avg,
    ]);
});


<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;


Route::get('/products', function (Request $request) {

    $products = DB::table('products')->get();

    $productsLessThan10 = DB::table('products')
        ->where('stock', '<', 10)
        ->get();


    $sum = DB::table('products')->sum('price');


    $avg = DB::table('products')->avg('price');

    return response()->json([
        'all_products' => $products,
        'low_stock_products' => $productsLessThan10,
        'total_price' => $sum,
        'average_price' => $avg,
    ]);
});



// Task 2: Eloquent ORM – Customer & Orders Relationship → 35 Marks
// ✅ Define Eloquent relationship between Customer and Order.

// Requirements:
// 1.       Customer → hasMany Order
// 2.       Order → belongsTo Customer
// 3.       Create a GET route /customer-orders/{id}
// 4.       Retrieve specific customer with all orders (use Eager Loading)
// 5.       Return JSON response with customer info and their orders.
// Template:
// Customer.php
class Customer extends Model {
    public function orders(): HasMany{
        return $this->hasMany(Order::class);
    }
}

// Order.php
class Order extends Model {
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;

Route::get('/customer-orders/{id}', function ($id) {
    // Find customer with orders (eager loading)
    $customer = Customer::with('orders')->find($id);
    return response()->json([
        'customer' => $customer,
        'orders' => $orders,
    ]);
});

<?php

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Customer extends Model
{

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}

class Order extends Model
{

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}

Route::get('/customer-orders/{id}', function ($id) {

    $customer = Customer::with('orders')->find($id);

    if (!$customer) {
        return response()->json([
            'status' => 'error',
            'message' => 'Customer not found.'
        ], 404);
    }
    return response()->json([
        'status' => 'success',
        'customer' => $customer,
    ]);
});


// Task 3: Sales Inventory – Insert & Report → 40 Marks
// ✅ Create a POST route /sales for inserting a new sale.
// ✅ Create a GET route /sales/report to show today’s and this month’s total sales.
// Requirements:
// 1.       Validate request (product_name, quantity, price).
// 2.       Insert into sales table.
// 3.       Using Query Builder:
// ·         Returns today's total sales.
// ·         Returns this month's total sales.
// Template:
use App\Http\Controllers\Auth\RegisterController;



use App\Http\Controllers\Auth\ResetPasswordController;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
// Don't forget to import the Validator facade!
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
Route::post('/sales', function (Request $request) {
    // Validate request


         $validator = Validator::make($request->all(), [
        'product_name' => 'required|string',
        'quantity' => 'required|integer|min:1',
        'price' => 'required|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    // Insert into sales table
    DB::table('sales')->insert([
        'product_name' => $request->product_name,
        'price' => $request->price,
        'quantity' => $request->quantity,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Sale inserted successfully'
    ]);
});

Route::get('/sales/report', function () {
    // Today’s total sales
    $todaySales = DB::table('sales')
        ->whereDate('created_at', Carbon::now())
        ->sum('quantity');
    // This month’s total sales
    $monthSales = DB::table('sales')
        ->whereMonth('created_at', Carbon::now()->month)
        ->whereYear('created_at', Carbon::now()->year)
        ->sum('quantity');
    return response()->json([
        'today_sales' => $todaySales,
        'month_sales' => $monthSales,
    ]);
});
