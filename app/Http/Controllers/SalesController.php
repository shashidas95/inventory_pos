<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function createInvoice(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $invoice = Invoice::create([
            'customer_id' => $request->customer_id,
            'user_id' => auth()->id(),
            'invoice_number' => 'INV-' . time(),
            'invoice_date' => now(),
            'total_amount' => 0,
            'status' => 'Pending',
        ]);

        $total = 0;

        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $amount = $product->price * $item['quantity'];

            InvoiceDetail::create([
                'invoice_id' => $invoice->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'amount' => $product->price,
                'total_amount' => $amount
            ]);

            // Reduce product stock
            $product->quantity -= $item['quantity'];
            $product->save();

            $total += $amount;
        }

        $invoice->total_amount = $total;
        $invoice->save();

        return response()->json(['status' => 'success', 'data' => $invoice]);
    }

    public function listInvoices()
    {
        $invoices = Invoice::with('customer', 'invoiceDetails.product')->latest()->get();
        return response()->json(['status' => 'success', 'data' => $invoices]);
    }
}
