<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;

use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Models\InvoiceDetail;
use App\Services\SaleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesController extends Controller
{

    protected $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    // 1️⃣ Page to create a sale
    public function salesPage()
    {
        $products = Product::all();
        $customers =  User::where('role', 'customer')->get(); // for dropdown
        return view('components.dashboard.admin.sales.create', compact('products', 'customers'));
    }

    // 2️⃣ Store Sale → Order → Invoice → Details with Transaction

    public function storeSale(Request $request)
    {

        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'products'    => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty'        => 'required|integer|min:1',
            'products.*.price'      => 'required|numeric|min:0',
            'total'       => 'required|numeric|min:0',
            'vat'         => 'required|numeric|min:0',
            'discount'    => 'required|numeric|min:0',
            'payable'     => 'required|numeric|min:0',
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

    // 3️⃣ List all sales/invoices
    public function listInvoices()
    {
        $invoices = Invoice::with('customer', 'invoiceDetails.product')->latest()->get();
        return view('components.dashboard.admin.sales.list', compact('invoices'));
    }

    // Optional: Show a single sale/invoice
    public function showInvoice($id)
    {
        $invoice = Invoice::with('customer', 'invoiceDetails.product', 'order.orderDetails.product')->findOrFail($id);
        return view('components.dashboard.admin.sales.show', compact('invoice'));
    }

    // Optional: Print invoice page
    public function printInvoice($id)
    {
        $invoice = Invoice::with('customer', 'invoiceDetails.product')->findOrFail($id);
        return view('components.dashboard.admin.sales.print', compact('invoice'));
    }
}
