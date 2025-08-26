<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid #333; }
        th, td { padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Invoice #{{ $invoice->invoice_number }}</h2>
        <p>Date: {{ $invoice->invoice_date }}</p>
        <p>Customer: {{ $invoice->customer->name }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->invoiceDetails as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->amount, 2) }}</td>
                    <td>{{ number_format($item->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Total: {{ number_format($invoice->total_amount, 2) }}</h3>
</body>
</html>
