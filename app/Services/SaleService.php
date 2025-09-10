<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Product;

class SaleService
{
    public function createSale(array $data)
    {
        DB::beginTransaction();

        try {
            // 1️⃣ Create Order
            $order = Order::create([
                'user_id' => $data['customer_id'],
                'store_id' => $data['store_id'],
                'status' => 'completed',
                'total' => 0,
            ]);

            $orderSubtotal = 0;       // sum of price * qty
            $invoiceSubtotal = 0;     // sum after per-line discount
            $invoiceTotal = 0;        // sum after VAT
            $vatPercentage = $data['vat'] ?? 0;
            $globalDiscount = $data['discount'] ?? 0;

            $invoiceDetailsData = [];

            // 2️⃣ Process Products
            foreach ($data['products'] as $item) {
                $item['price'] = floatval($item['price']);
                $item['qty']   = intval($item['qty']);
                $itemDiscount  = floatval($item['discount'] ?? 0);

                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                // Check & deduct store stock
                $storeStock = $product->stores()->where('store_id', $data['store_id'])->first();
                if (!$storeStock || $storeStock->pivot->quantity < $item['qty']) {
                    throw new \Exception("Insufficient stock for product {$product->name} in store ID {$data['store_id']}");
                }

                $product->stores()->updateExistingPivot($data['store_id'], [
                    'quantity' => $storeStock->pivot->quantity - $item['qty'],
                ]);

                // Line calculations
                $lineTotal = $item['price'] * $item['qty'];
                $lineSubtotal = max(0, $lineTotal - $itemDiscount);
                $lineVatAmount = $lineSubtotal * ($vatPercentage / 100);
                $lineFinalTotal = $lineSubtotal + $lineVatAmount;

                $orderSubtotal += $lineTotal;
                $invoiceSubtotal += $lineSubtotal;
                $invoiceTotal += $lineFinalTotal;

                // Order details
                $order->details()->create([
                    'product_id'   => $product->id,
                    'store_id'     => $data['store_id'],
                    'quantity'     => $item['qty'],
                    'price'        => $item['price'],
                    'unit_price'   => $item['price'],
                    'total_amount' => $lineTotal,
                ]);

                // Invoice details
                $invoiceDetailsData[] = [
                    'product_id'      => $product->id,
                    'store_id'        => $data['store_id'],
                    'quantity'        => $item['qty'],
                    'unit_price'      => $item['price'],
                    'total_amount'    => $lineTotal,
                    'discount_amount' => $itemDiscount,
                    'subtotal_amount' => $lineSubtotal,
                    'vat_percentage'  => $vatPercentage,
                    'vat_amount'      => $lineVatAmount,
                    'final_total'     => $lineFinalTotal,
                ];
            }

            // 3️⃣ Update order total
            $order->update(['total' => $invoiceTotal - $globalDiscount]);

            // 4️⃣ Create invoice
            $invoice = Invoice::create([
                'customer_id'    => $data['customer_id'],
                'user_id'        => auth()->id(),
                'store_id'       => $data['store_id'],
                'order_id'       => $order->id,
                'invoice_number' => 'INV-' . time(),
                'invoice_date'   => now(),
                'total_amount'   => $orderSubtotal,
                'subtotal_amount' => $invoiceSubtotal,
                'vat_percentage' => $vatPercentage,
                'vat_amount'     => $invoiceTotal - $invoiceSubtotal,
                'discount_amount' => $globalDiscount,
                'final_total'    => max(0, $invoiceTotal - $globalDiscount),
                'status'         => 'paid',
            ]);

            // 5️⃣ Save invoice details
            foreach ($invoiceDetailsData as $detail) {
                $invoice->invoiceDetails()->create($detail);
            }

            Log::info("Order subtotal: $orderSubtotal, Invoice subtotal: $invoiceSubtotal, VAT: " . ($invoiceTotal - $invoiceSubtotal) . ", Discount: $globalDiscount, Final total: " . ($invoiceTotal - $globalDiscount));

            DB::commit();
            return $invoice;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sale creation failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
