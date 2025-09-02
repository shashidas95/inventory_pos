<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Invoice #{{ $invoice->invoice_number }}</h1>
    <p>User: {{ $invoice->user->name ?? 'N/A' }}</p>
    <p>Customer: {{ $invoice->customer->name ?? 'N/A' }}</p>
    <p>Date: {{ $invoice->invoice_date->format('d-m-Y') }}</p>
    <p>Status: {{ ucfirst($invoice->status) }}</p>

    <table class="w-full mt-4 border border-gray-300">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-4 py-2 text-left">Product</th>
                <th class="border px-4 py-2 text-center">Quantity</th>
                <th class="border px-4 py-2 text-right">Unit Price ($)</th>
                <th class="border px-4 py-2 text-right">Total ($)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->invoiceDetails as $detail)
                <tr>
                    <td class="border px-4 py-2">{{ $detail->product->name ?? 'N/A' }}</td>
                    <td class="border px-4 py-2 text-center">{{ $detail->quantity }}</td>
                    <td class="border px-4 py-2 text-right">{{ number_format($detail->amount, 2) }}</td>
                    <td class="border px-4 py-2 text-right">{{ number_format($detail->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4 text-right">
        <p>Subtotal: ${{ number_format($invoice->subtotal_amount, 2) }}</p>
        <p>VAT ({{ $invoice->vat_percentage }}%): ${{ number_format($invoice->vat_amount, 2) }}</p>
        <p>Discount: ${{ number_format($invoice->discount_amount, 2) }}</p>
        <h5 class="font-bold">Total Payable: ${{ number_format($invoice->final_total, 2) }}</h5>
    </div>

    <div class="mt-4">
        <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="btn btn-primary">
            Print Invoice
        </a>
    </div>
</div>
