<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\OrderDetail;
use App\Models\InvoiceDetail;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class InvoiceDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $invoices = Invoice::all();

        foreach ($invoices as $invoice) {
            $orderDetails = OrderDetail::where('order_id', $invoice->order_id)->get();
            $vatPercentage = $invoice->vat_percentage;

            foreach ($orderDetails as $detail) {
                // Use data from the corresponding order detail
                $unitPrice = $detail->price;
                $quantity = $detail->quantity;
                $totalAmount = $unitPrice * $quantity;
                $discount = fake()->randomFloat(2, 0, $totalAmount * 0.05);
                $subtotalAmount = $totalAmount - $discount;
                $vatAmount = $subtotalAmount * ($vatPercentage / 100);
                $finalTotal = $subtotalAmount + $vatAmount;

                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $detail->product_id,
                    'store_id' => $invoice->store_id, // Added from the parent invoice
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_amount' => $totalAmount,
                    'discount_amount' => $discount,
                    'subtotal_amount' => $subtotalAmount,
                    'vat_percentage' => $vatPercentage,
                    'vat_amount' => $vatAmount,
                    'final_total' => $finalTotal,
                ]);
            }
        }
    }
}
