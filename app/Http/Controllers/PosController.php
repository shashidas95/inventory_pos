<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CheckoutRequest;

class PosController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }
    public function checkout(CheckoutRequest $request)
    {
        return DB::transaction(function () use ($request) {

            $validated = $request->validated();


            // 1. Create Order
            $order = Order::create([
                'user_id'  => $request->user_id,
                'store_id' => $request->store_id,
                'status'   => 'completed',
                'total'    => collect($request->items)->sum(fn($i) => $i['price'] * $i['quantity']),
            ]);

            // 2. Order Details + stock deduction
            foreach ($request->items as $item) {
                $order->details()->create([
                    'product_id'   => $item['product_id'],
                    'store_id'     => $request->store_id,
                    'quantity'     => $item['quantity'],
                    'price'        => $item['price'],
                    'total_amount' => $item['price'] * $item['quantity'],
                ]);

                Product::where('id', $item['product_id'])
                    ->decrement('stock', $item['quantity']);
            }

            // 3. Generate Invoice (with VAT/discount if needed)
            $invoice = Invoice::createFromOrder($order, vat: 5, discount: 0);

            return response()->json([
                'message' => 'Sale completed successfully',
                'order'   => $order,
                'invoice' => $invoice->load('details'),
            ]);
        });
    }
    public function printReceipt($invoiceId)
    {
        $invoice = Invoice::with(['store', 'customer', 'details.product'])->findOrFail($invoiceId);
        return view('components.dashboard.admin.receipts.thermal', compact('invoice'));
    }
}
