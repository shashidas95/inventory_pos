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


{{--
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h1, h2, h3 { margin: 0; padding: 0; }
        .header, .footer { text-align: center; }
        .invoice-info, .customer-info, .items-table { margin-top: 20px; width: 100%; }
        .items-table table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table table, .items-table th, .items-table td { border: 1px solid #000; }
        .items-table th, .items-table td { padding: 8px; text-align: left; }
        .total { margin-top: 20px; text-align: right; font-size: 1.1em; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Your Company Name</h1>
        <p>Address | Phone | Email</p>
    </div>

    <div class="invoice-info">
        <h2>Invoice #{{ $invoice->id }}</h2>
        <p>Date: {{ $invoice->created_at->format('d M, Y') }}</p>
        <p>Status: {{ ucfirst($invoice->status) }}</p>
    </div>

    <div class="customer-info">
        <h3>Customer Information</h3>
        <p>Name: {{ $invoice->user->name ?? '-' }}</p>
        <p>Email: {{ $invoice->user->email ?? '-' }}</p>
    </div>

    <div class="items-table">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->details as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 2) }}</td>
                        <td>{{ number_format($item->quantity * $item->price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="total">
        <strong>Total: {{ number_format($invoice->total, 2) }}</strong>
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
    </div>
</body>
</html> --}}
