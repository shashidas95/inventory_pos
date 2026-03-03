<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $completedOrders = Order::where('status', 'completed')->get();
        $staffUsers = User::whereNotNull('store_id')->pluck('id')->toArray();

        foreach ($completedOrders as $order) {
            $vatPercentage = 10.00;
            $discount = fake()->randomFloat(2, 0, $order->total * 0.1);
            $subtotalAmount = $order->total - $discount;
            $vatAmount = $subtotalAmount * ($vatPercentage / 100);
            $finalTotal = $subtotalAmount + $vatAmount;

            // Assign a staff member to the invoice (or null if no staff available)
            $userId = !empty($staffUsers) ? fake()->randomElement($staffUsers) : null;

            Invoice::create([
                'customer_id' => $order->user_id,
                'user_id' => $userId, // Staff member who created the invoice
                'store_id' => $order->store_id,
                'order_id' => $order->id,
                'invoice_number' => 'INV-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'invoice_date' => fake()->dateTimeBetween($order->created_at, 'now'),
                'total_amount' => $order->total,
                'discount_amount' => $discount,
                'subtotal_amount' => $subtotalAmount,
                'vat_percentage' => $vatPercentage,
                'vat_amount' => $vatAmount,
                'final_total' => $finalTotal,
                'notes' => fake()->optional()->sentence(),
                'status' => 'Paid', // Assuming completed orders result in paid invoices
            ]);
        }
    }
}
