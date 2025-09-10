<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\OrderDetail;
use App\Models\InvoiceDetail;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesController extends Controller
{
    protected $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    // 1️⃣ Page to create a sale (POS)
    public function salesPage(Request $request)
    {
        $storeId = $request->store_id ?? null;

        // Products filtered by store stock if store_id is given
        $products = $storeId
            ? Product::whereHas('stores', fn($q) => $q->where('store_id', $storeId))->get()
            : Product::all();

        $customers = User::where('role', 'customer')->get(); // For dropdown
        return view('components.dashboard.admin.sales.create', compact('products', 'customers', 'storeId'));
    }

    // 2️⃣ Store Sale → Order → Invoice → Details (with Transaction & store_id)
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

    // 3️⃣ List all sales/invoices (Blade view)
    public function listInvoices(Request $request)
    {
        $storeId = $request->store_id ?? null;

        $invoices = Invoice::with(['customer', 'invoiceDetails.product', 'order'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->latest()
            ->get();

        return view('components.dashboard.admin.sales.list', compact('invoices', 'storeId'));
    }

    // 4️⃣ Show single sale/invoice
    public function showInvoice($id)
    {
        $invoice = Invoice::with(['customer', 'invoiceDetails.product', 'order.orderDetails.product', 'order'])
            ->findOrFail($id);

        return view('components.dashboard.admin.sales.show', compact('invoice'));
    }

    // 5️⃣ Print invoice page
    public function printInvoice($id)
    {
        $invoice = Invoice::with(['customer', 'invoiceDetails.product', 'order'])
            ->findOrFail($id);

        return view('components.dashboard.admin.sales.print', compact('invoice'));
    }
}
