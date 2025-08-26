<div class="container">
    <h4>All Orders</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#Order ID</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Total</th>
                <th>Date</th>
                <th>Invoice</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->user->name }}</td>
                <td>{{ ucfirst($order->status) }}</td>
                <td>${{ $order->total }}</td>
                <td>{{ $order->created_at->format('Y-m-d') }}</td>
                <td>
                    @if($order->invoice)
                        <a href="{{ route('admin.invoice.show', $order->invoice->id) }}" class="btn btn-sm btn-info">Invoice</a>
                    @else
                        <span class="text-muted">No Invoice</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>