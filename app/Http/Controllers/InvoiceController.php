<?php

namespace App\Http\Controllers;

use Log;
use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Models\InvoiceDetail;
use App\Services\SaleService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    protected $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    // List all invoices (Admin Blade view)
    public function listInvoices()
    {
        $invoices = Invoice::with('customer', 'invoiceDetails.product', 'user')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('pages.dashboard.admin.invoices.invoice-page', compact('invoices'));
    }
    public function customerInvoices()
    {
        $user = auth()->user();
        $invoices = Invoice::where('customer_id', $user->id)->latest()->get();

        return view('pages.dashboard.customers.invoices.index', compact('invoices'));
    }


    // Show single invoice (Admin Blade view)
    public function show($id)
    {
        $invoice = Invoice::with([
            'order.details.product', // load order + its products
            'invoiceDetails.product',
            'user',
            'customer'
        ])->findOrFail($id);

        return view('pages.dashboard.admin.invoices.show', compact('invoice'));
    }

    // Print invoice (Admin Blade view)

    public function printInvoice($id)
    {
        $invoice = Invoice::with([
            'order.details.product',
            'invoiceDetails.product',
            'user',
            'customer'
        ])->findOrFail($id);

        return view('pages.dashboard.admin.invoices.print', compact('invoice'));
    }



    // POS / Sale creation using SaleService
    public function storeSale(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'products'    => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty'        => 'required|integer|min:1',
            'products.*.price'      => 'required|numeric|min:0',
            'vat'         => 'required|numeric|min:0',
            'discount'    => 'required|numeric|min:0',
        ]);

        try {
            $invoice = $this->saleService->createSale($request->all());
            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->id
            ]);
        } catch (\Exception $e) {
            Log::error('POS sale failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Sale creation failed: ' . $e->getMessage()
            ], 500);
        }
    }
    // ✅ Add createInvoice here
    public function createInvoice(Request $request, $orderId)
    {
        $order = Order::with('details.product', 'user')->findOrFail($orderId);

        // Step 1: Calculate subtotal
        $subtotal = $order->details->sum(fn($item) => $item->quantity * $item->price);

        // Step 2: VAT & Discount
        $vatPercentage  = $request->vat_percentage ?? 10; // default 10%
        $discountAmount = $request->discount_amount ?? 0;

        $vatAmount  = $subtotal * ($vatPercentage / 100);
        $finalTotal = $subtotal + $vatAmount - $discountAmount;

        // Step 3: Create Invoice
        $invoice = Invoice::create([
            'customer_id'     => $order->user_id,
            'user_id'         => auth()->id(),
            'order_id'        => $order->id,
            'invoice_number'  => 'INV-' . Str::random(8),
            'invoice_date'    => now(),
            'subtotal_amount' => $subtotal,
            'vat_percentage'  => $vatPercentage,
            'vat_amount'      => $vatAmount,
            'discount_amount' => $discountAmount,
            'final_total'     => $finalTotal,
            'status'          => 'paid',
            'notes'           => $request->notes ?? null
        ]);

        // Step 4: Create Invoice Details
        foreach ($order->details as $detail) {
            InvoiceDetail::create([
                'invoice_id'   => $invoice->id,
                'product_id'   => $detail->product_id,
                'quantity'     => $detail->quantity,
                'amount'       => $detail->price,
                'total_amount' => $detail->quantity * $detail->price,
            ]);
        }

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Invoice created successfully!');
    }



    // Dashboard stats
    public function getInvoiceStats(Request $request, $orderId)
    {

        $user = auth()->user();


        if ($user->role === 'admin') {
            $totalInvoices = Invoice::count();
            $totalAmount = Invoice::sum('total_amount');
        } else {
            $totalInvoices = Invoice::where('customer_id', $user->id)->count();
            $totalAmount = Invoice::where('customer_id', $user->id)->sum('final_total');
        }


        return response()->json([
            'total_invoices' => $totalInvoices,
            'total_amount' => $totalAmount
        ]);
    }
}
