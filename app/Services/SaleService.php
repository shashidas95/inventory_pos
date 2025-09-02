<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Product;

class SaleService
{
    public function createSale(array $data)
    {
        DB::beginTransaction();

        try {
            // 1. Create Order
            $order = Order::create([
                'user_id' => $data['customer_id'],
                'status'  => 'completed',
                'total'   => 0, // will calculate later
            ]);

            $subtotal = 0;

            // 2. Process products
            foreach ($data['products'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->quantity < $item['qty']) {
                    throw new \Exception("Insufficient stock for product {$product->name}");
                }

                $product->quantity -= $item['qty'];
                $product->save();

                $lineTotal = $item['price'] * $item['qty'];
                $subtotal += $lineTotal;

                // Order Details
                $order->details()->create([
                    'product_id' => $product->id,
                    'quantity'   => $item['qty'],
                    'price'      => $item['price'],
                ]);
            }

            // 3. Calculate VAT & Discount
            $vatPercentage  = $data['vat'] ?? 5;
            $discountAmount = $data['discount'] ?? 0;

            $vatAmount  = $subtotal * ($vatPercentage / 100);
            $finalTotal = $subtotal + $vatAmount - $discountAmount;

         

            // Update order total
            $order->total = $finalTotal;
            $order->save();

            // 4. Create Invoice
            $invoice = Invoice::create([
                'customer_id'     => $data['customer_id'],
                'user_id'         => auth()->id(),
                'order_id'        => $order->id,
                'invoice_number'  => 'INV-' . time(),
                'invoice_date'    => now(),
                'subtotal_amount' => $subtotal,
                'vat_percentage'  => $vatPercentage,
                'vat_amount'      => $vatAmount,
                'discount_amount' => $discountAmount,
                'final_total'     => $finalTotal,
                'status'          => 'paid',
            ]);

            // 5. Create Invoice Details
            foreach ($data['products'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $invoice->invoiceDetails()->create([
                    'product_id'   => $product->id,
                    'quantity'     => $item['qty'],
                    'amount'       => $item['price'],
                    'total_amount' => $item['price'] * $item['qty'],
                ]);
            }

            DB::commit();
            return $invoice;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sale creation failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
