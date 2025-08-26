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
                <th>Action</th>
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
                    @if($order->status == 'pending')
                        <button class="btn btn-sm btn-success" onclick="confirmOrder('{{ $order->id }}', '{{ $order->user_id }}')">Confirm</button>
                    @else
                        <span class="text-muted">Confirmed</span>
                    @endif
                </td>
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


@push('script')

    <script>
        function confirmOrder(orderID, customerID)
        {
            if(confirm('Are you sure to confirm?')){

                axios.post('/backend/invoices/store', {
                    'order_id':orderID,
                    'customer_id':customerID,
                }).then(function(response){
                     console.log(response)   
                })

            }
        }
    </script>

@endpush