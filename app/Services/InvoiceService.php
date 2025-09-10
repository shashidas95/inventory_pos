<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Invoice;

class InvoiceService
{
    public function createFromOrder(Order $order, int $vat = 0, int $discount = 0): Invoice
    {
        // Calculate subtotal from order items
        $subtotal = $order->items->sum(fn($i) => $i->price * $i->quantity);

        // Calculate totals
        $vatAmount = $subtotal * ($vat / 100);
        $total = $subtotal + $vatAmount - $discount;

        // Create invoice
        $invoice = Invoice::create([
            'order_id'   => $order->id,
            'subtotal'   => $subtotal,
            'vat'        => $vat,
            'discount'   => $discount,
            'vat_amount' => $vatAmount,
            'total'      => $total,
        ]);

        // Optionally create invoice details (if you store product breakdown)
        foreach ($order->items as $item) {
            $invoice->details()->create([
                'product_id' => $item->product_id,
                'price'      => $item->price,
                'quantity'   => $item->quantity,
                'line_total' => $item->price * $item->quantity,
            ]);
        }

        return $invoice;
    }
}
