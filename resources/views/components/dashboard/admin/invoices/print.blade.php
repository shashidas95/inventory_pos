<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <link href="{{ asset('assets/css/bootstrap.css') }}" rel="stylesheet" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .mb-2 {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Invoice #{{ $invoice->invoice_number }}</h2>
        <p><strong>Customer:</strong> {{ $invoice->customer->name ?? 'N/A' }}</p>
        <p><strong>Created By:</strong> {{ $invoice->user->name ?? 'N/A' }}</p>
        <p><strong>Date:</strong> {{ $invoice->invoice_date->format('d-m-Y') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>

        <table class="mt-3 mb-3">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-right">Unit Price ($)</th>
                    <th class="text-right">Total ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->invoiceDetails as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detail->product->name ?? 'N/A' }}</td>
                        <td class="text-center">{{ $detail->quantity }}</td>
                        <td class="text-right">{{ number_format($detail->amount, 2) }}</td>
                        <td class="text-right">{{ number_format($detail->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">Subtotal</td>
                    <td class="text-right">${{ number_format($invoice->subtotal_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-right">VAT ({{ $invoice->vat_percentage }}%)</td>
                    <td class="text-right">${{ number_format($invoice->vat_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-right">Discount</td>
                    <td class="text-right">${{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                <tr class="font-weight-bold">
                    <td colspan="4" class="text-right">Total Payable</td>
                    <td class="text-right">${{ number_format($invoice->final_total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if ($invoice->notes)
            <p><strong>Notes:</strong> {{ $invoice->notes }}</p>
        @endif

        <button onclick="window.print()" class="btn btn-primary mt-3">Print Invoice</button>
    </div>
</body>

</html>
