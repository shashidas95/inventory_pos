{{-- resources/views/customer/orders/list.blade.php --}}
@extends('layouts.sidenav-layout')

@section('content')
    <div class="container-fluid">
        <h4 class="mb-3">My Orders</h4>

        <div class="table-responsive">
            <table class="table table-bordered" id="customerOrdersTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Order ID</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Products</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $order->id }}</td>
                            <td>{{ ucfirst($order->status) }}</td>
                            <td>${{ number_format($order->total, 2) }}</td>
                            <td>
                                @foreach ($order->details as $item)
                                    {{ $item->product->name }} (x{{ $item->quantity }})<br>
                                @endforeach
                            </td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('customer.order.list', $order->id) }}"
                                    class="btn btn-sm btn-primary">View</a>
                                {{-- Optional: add Cancel button if order is pending --}}
                                {{-- @if ($order->status === 'pending')
                            <form action="{{ route('customer.order.cancel', $order->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                            </form>
                        @endif --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            $('#customerOrdersTable').DataTable({
                "order": [
                    [0, "desc"]
                ],
                "pageLength": 10
            });
        });
    </script>
@endpush
