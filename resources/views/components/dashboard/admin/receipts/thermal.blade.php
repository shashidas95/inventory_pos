<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Receipt #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: monospace, sans-serif;
            font-size: 12px;
            width: 58mm;
            /* thermal paper width */
            margin: 0 auto;
        }

        .center {
            text-align: center;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 2px 0;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="center">
        <h3>{{ $invoice->store->name }}</h3>
        <p>{{ $invoice->store->address }}</p>
        <p>Tel: {{ $invoice->store->phone ?? '-' }}</p>
    </div>
    <div class="line"></div>

    <!-- Invoice info -->
    <p>
        Invoice #: {{ $invoice->invoice_number }}<br>
        Date: {{ $invoice->invoice_date->format('d/m/Y H:i') }}<br>
        Customer: {{ $invoice->customer->name ?? 'Walk-in' }}
    </p>
    <div class="line"></div>

    <!-- Products -->
    <table>
        @foreach ($invoice->details as $detail)
            <tr>
                <td colspan="2">{{ $detail->product->name }}</td>
            </tr>
            <tr>
                <td>{{ $detail->quantity }} x {{ number_format($detail->unit_price, 2) }}</td>
                <td class="right">{{ number_format($detail->final_total, 2) }}</td>
            </tr>
        @endforeach
    </table>
    <div class="line"></div>

    <!-- Totals -->
    <table>
        <tr>
            <td>Total:</td>
            <td class="right">{{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Discount:</td>
            <td class="right">-{{ number_format($invoice->discount_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Subtotal:</td>
            <td class="right">{{ number_format($invoice->subtotal_amount, 2) }}</td>
        </tr>
        <tr>
            <td>VAT ({{ $invoice->vat_percentage }}%):</td>
            <td class="right">{{ number_format($invoice->vat_amount, 2) }}</td>
        </tr>
        <tr class="bold">
            <td>Grand Total:</td>
            <td class="right">{{ number_format($invoice->final_total, 2) }}</td>
        </tr>
    </table>
    <div class="line"></div>

    <!-- Footer -->
    <div class="center">
        <p>Thank you for shopping!</p>
        <p>Powered by YourPOS</p>
    </div>
</body>

</html>
