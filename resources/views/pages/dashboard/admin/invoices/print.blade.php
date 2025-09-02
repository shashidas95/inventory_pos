<!DOCTYPE html>
<html>

<head>
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Invoice #{{ $invoice->invoice_number }}</h2>
            <button class="btn btn-primary no-print" onclick="window.print()">Print</button>
        </div>

        <div class="mb-3">
            <p><strong>Customer:</strong> {{ $invoice->customer->name ?? 'Walk-in' }}</p>
            <p><strong>Created By:</strong> {{ $invoice->user->name ?? 'N/A' }}</p>
            <p><strong>Date:</strong> {{ $invoice->invoice_date ? $invoice->invoice_date->format('d-m-Y') : 'N/A' }}</p>
            <p><strong>Status:</strong> {{ ucfirst($invoice->status ?? 'N/A') }}</p>
        </div>

        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price ($)</th>
                    <th>Subtotal ($)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoice->invoiceDetails as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price ?? $item->amount, 2) }}</td>
                        <td>{{ number_format($item->quantity * ($item->price ?? $item->amount), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No items found</td>
                    </tr>
                @endforelse
            </tbody>


            <tfoot>
                <tr class="font-bold">
                    <td colspan="4" class="text-end">Grand Total</td>
                    <td class="text-end">${{ number_format($invoice->total_amount ?? 0, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if (!empty($invoice->notes))
            <p class="mt-3"><strong>Notes:</strong> {{ $invoice->notes }}</p>
        @endif
    </div>
</body>

</html>
