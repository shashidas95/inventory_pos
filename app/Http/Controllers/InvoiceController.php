<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function printInvoice($id)
    {
        $invoice = Invoice::with('customer', 'invoiceDetails.product')->findOrFail($id);
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
    // List all invoices
    public function listInvoices()
    {
        // Fetch invoices with customer and details
        $invoices = Invoice::with('customer', 'invoiceDetails.product')
            ->orderBy('invoice_date', 'desc')
            ->get();

        // Return as JSON (for API) or view (if using blade)
        return view('components.dashbord.admin.invoices.list', compact('invoices'));
    }
}
