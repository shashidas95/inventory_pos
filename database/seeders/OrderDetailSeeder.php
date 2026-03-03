<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetail;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrderDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::all();
        $productIds = Product::pluck('id')->toArray();

        foreach ($orders as $order) {
            $orderTotal = 0;
            $numItems = rand(1, 4);
            $selectedProductIds = fake()->randomElements($productIds, $numItems, true); // True allows duplicates

            foreach (array_unique($selectedProductIds) as $productId) {
                $product = Product::find($productId);
                $quantity = rand(1, 5);
                $price = $product->price;
                $totalAmount = $price * $quantity;
                $orderTotal += $totalAmount;

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'store_id' => $order->store_id, // Inherit store from parent order
                    'quantity' => $quantity,
                    'price' => $price,
                    'total_amount' => $totalAmount,
                ]);
            }

            // Update the parent order's total amount for accuracy
            $order->total = $orderTotal;
            $order->save();
        }
    }
}
