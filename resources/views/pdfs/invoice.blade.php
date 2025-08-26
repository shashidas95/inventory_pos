<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
</head>
<body>

    <h1>Invoice for {{ $invoice->customer_id}}</h1>

    <table>
        <tr>
            <th>sl</th>
            <th>Product</th>
        </tr>
        <tr>
            <td>1</td>
            <td>Product 1</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Product 2</td>
        </tr>
    </table>

    
</body>
</html>