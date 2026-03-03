<?php

namespace App\Http\Controllers;

use Log;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use Illuminate\Http\Request;
use App\Services\SaleService;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    protected $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    // Admin: List all invoices (Blade view)
    // 🔹 List invoices
    // 🔹 API: List invoices (JSON)
    public function index(Request $request)
    {
        $user = auth()->user();

        $invoices = Invoice::with(['customer', 'invoiceDetails'])
            ->when($user->role === 'manager', function ($query) use ($user) {
                $query->where('store_id', $user->store_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($invoices);
    }

    // 🔹 API: Show single invoice (JSON)
    public function apiShow($id)
    {
        $user = auth()->user();

        $invoice = Invoice::with(['customer', 'invoiceDetails.product'])
            ->when($user->role === 'manager', function ($query) use ($user) {
                $query->where('store_id', $user->store_id);
            })
            ->findOrFail($id);

        return response()->json($invoice);
    }

    // 🔹 Blade: List invoices (admin/manager dashboard)
    public function listInvoices()
    {
        $user = auth()->user();

        $invoices = Invoice::with(['customer', 'store', 'invoiceDetails.product', 'user'])
            ->when($user->role === 'manager', function ($query) use ($user) {
                $query->where('store_id', $user->store_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.dashboard.admin.invoices.invoice-page', compact('invoices'));
    }

    // 🔹 Customer: List own invoices
    public function customerInvoices()
    {
        $user = auth()->user();

        $invoices = Invoice::with(['store', 'invoiceDetails.product'])
            ->where('customer_id', $user->id)
            ->latest()
            ->get();

        return view('pages.dashboard.customers.invoices.index', compact('invoices'));
    }

    // 🔹 Blade: Show single invoice
    public function show($id)
    {
        $user = auth()->user();

        $invoice = Invoice::with([
            'order.details.product',
            'invoiceDetails.product',
            'user',
            'customer',
            'store'
        ])
            ->when($user->role === 'manager', function ($query) use ($user) {
                $query->where('store_id', $user->store_id);
            })
            ->findOrFail($id);

        return view('pages.dashboard.admin.invoices.show', compact('invoice'));
    }

    // 🔹 Blade: Print invoice
    public function printInvoice($id)
    {
        $user = auth()->user();

        $invoice = Invoice::with([
            'order.details.product',
            'invoiceDetails.product',
            'user',
            'customer',
            'store'
        ])
            ->when($user->role === 'manager', function ($query) use ($user) {
                $query->where('store_id', $user->store_id);
            })
            ->findOrFail($id);

        return view('pages.dashboard.admin.invoices.print', compact('invoice'));
    }

    // POS / Sale creation using SaleService
    public function storeSale(Request $request)
    {
        $request->validate([
            'customer_id'  => 'required|exists:users,id',
            'products'     => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty'        => 'required|integer|min:1',
            'products.*.price'      => 'required|numeric|min:0',
            'total'        => 'required|numeric|min:0',
            'vat'          => 'required|numeric|min:0',
            'discount'     => 'required|numeric|min:0',
            'payable'      => 'required|numeric|min:0',
        ]);

        try {
            $user = auth()->user();
            $storeId = $user->role === 'manager' ? $user->store_id : $request->input('store_id');

            if (!$storeId) {
                return response()->json(['message' => 'Store ID required'], 422);
            }

            $data = $request->all();
            $data['store_id'] = $storeId;

            $invoice = $this->saleService->createSale($data);

            return response()->json([
                'success'    => true,
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


    // Create Invoice from existing order
    public function createInvoice(Request $request, $orderId)
    {
        $user = auth()->user();

        try {
            return DB::transaction(function () use ($user, $orderId) {

                // 🔐 Fetch order with store_id check
                $order = Order::where('id', $orderId)
                    ->when($user->role === 'manager', function ($query) use ($user) {
                        $query->where('store_id', $user->store_id);
                    })
                    ->firstOrFail();

                // Create Invoice
                $invoice = new Invoice();
                $invoice->order_id   = $order->id;
                $invoice->store_id   = $order->store_id;
                $invoice->customer_id = $order->customer_id;
                $invoice->user_id    = $user->id;
                $invoice->total_amount = $order->total_amount;
                $invoice->discount_amount = $order->discount_amount ?? 0;
                $invoice->vat_amount = $order->vat_amount ?? 0;
                $invoice->final_total = $order->final_total ?? $order->total_amount;
                $invoice->save();

                // Insert invoice details
                foreach ($order->orderDetails as $detail) {
                    InvoiceDetail::create([
                        'invoice_id'       => $invoice->id,
                        'product_id'       => $detail->product_id,
                        'store_id'         => $order->store_id, // ✅ enforce store_id
                        'quantity'         => $detail->quantity,
                        'unit_price'       => $detail->unit_price,
                        'total_amount'     => $detail->total_amount,
                        'discount_amount'  => $detail->discount_amount ?? 0,
                        'subtotal_amount'  => $detail->subtotal_amount ?? $detail->total_amount,
                        'vat_percentage'   => $detail->vat_percentage ?? 0,
                        'vat_amount'       => $detail->vat_amount ?? 0,
                        'final_total'      => $detail->final_total ?? $detail->total_amount,
                    ]);
                }

                return response()->json([
                    'message' => 'Invoice created successfully',
                    'invoice' => $invoice->load('invoiceDetails'),
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error('Invoice creation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Invoice creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    // Dashboard stats (store-aware)
    public function getInvoiceStats()
    {
        $user = auth()->user();

        if ($user->role === 'admin' || $user->role === 'super_admin') {
            $totalInvoices = Invoice::count();
            $totalAmount   = Invoice::sum('final_total');
        } else { // manager
            $totalInvoices = Invoice::where('store_id', $user->store_id)->count();
            $totalAmount   = Invoice::where('store_id', $user->store_id)->sum('final_total');
        }

        return response()->json([
            'total_invoices' => $totalInvoices,
            'total_amount'   => $totalAmount
        ]);
    }

    // Delete an invoice (store-aware)
    public function destroy($id)
    {
        $user = auth()->user();
        $invoice = Invoice::findOrFail($id);

        if ($user->role === 'manager' && $invoice->store_id !== $user->store_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this invoice.'
            ], 403);
        }

        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted successfully.'
        ]);
    }

    // Update an invoice (store-aware)
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $invoice = Invoice::findOrFail($id);

        if ($user->role === 'manager' && $invoice->store_id !== $user->store_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this invoice.'
            ], 403);
        }

        $validated = $request->validate([
            'vat_percentage' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:paid,unpaid,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        $invoice->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully.',
            'invoice' => $invoice
        ]);
    }
    public function storeScopedIndex()
    {
        $user = auth()->user();

        // 1. Role Check: Ensure the authenticated user is actually a manager.
        if ($user->role !== 'manager') {
            abort(403, 'Unauthorized access.');
        }

        $storeId = $user->store_id;

        if (!$storeId) {
            return view('manager.invoices.list')->with('invoices', collect([]));
        }

        // Invoice filtering is simple because 'store_id' should be a direct field on the Invoice model.
        $invoices = Invoice::where('store_id', $storeId)->latest()->paginate(20);

        return view('manager.invoices.list', compact('invoices'));
    }
}
