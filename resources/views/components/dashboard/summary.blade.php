<div class="container-fluid">
    <div class="row">

        {{-- Dashboard Cards --}}
        @php
            if ($user->role === 'admin') {
                $cards = [
                    [
                        'id' => 'product',
                        'label' => 'Products',
                        'currency' => false,
                        'value' => $productsCount,
                        'url' => route('admin.products.adminProductList'),
                    ],
                    [
                        'id' => 'category',
                        'label' => 'Categories',
                        'currency' => false,
                        'value' => $categoriesCount,
                        'url' => route('categories.index'),
                    ],
                    // [
                    //     'id' => 'customer',
                    //     'label' => 'Customers',
                    //     'currency' => false,
                    //     'value' => $customersCount,
                    //     'url' => route('customers.list'),
                    // ],
                    [
                        'id' => 'invoice',
                        'label' => 'Invoices',
                        'currency' => false,
                        'value' => $invoicesCount,
                        'url' => route('invoices.list'),
                    ],
                    [
                        'id' => 'orders',
                        'label' => 'Orders',
                        'currency' => false,
                        'value' => $ordersCount,
                        'url' => route('orders.list'),
                    ],
                    [
                        'id' => 'total',
                        'label' => 'Total Sale',
                        'currency' => true,
                        'value' => $totalSales,
                        'url' => route('invoices.list'),
                    ],
                    // [
                    //     'id' => 'vat',
                    //     'label' => 'VAT Collection',
                    //     'currency' => true,
                    //     'value' => $vat,
                    //     'url' => route('invoices.list'),
                    // ],
                    // [
                    //     'id' => 'payable',
                    //     'label' => 'Total Collection',
                    //     'currency' => true,
                    //     'value' => $payable,
                    //     'url' => route('invoices.list'),
                    // ],
                ];
            } else {
                $cards = [
                    [
                        'id' => 'my-invoices',
                        'label' => 'My Invoices',
                        'currency' => false,
                        'value' => $totalInvoices,
                        'url' => route('customer.invoices'),
                    ],
                    [
                        'id' => 'my-orders',
                        'label' => 'My Orders',
                        'currency' => false,
                        'value' => $totalOrders,
                        'url' => route('customer.order.list'),
                    ],
                    [
                        'id' => 'total-spent',
                        'label' => 'Total Spent',
                        'currency' => true,
                        'value' => $totalSpent,
                        'url' => route('customer.invoices'),
                    ],
                ];
            }
        @endphp

        @foreach ($cards as $card)
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 p-2">
                <a href="{{ $card['url'] }}" class="text-decoration-none text-dark">
                    <div class="card h-100 bg-white shadow-sm">
                        <div class="p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 font-weight-bold">
                                    @if ($card['currency'])
                                        $
                                    @endif
                                    <span id="{{ $card['id'] }}">{{ $card['value'] }}</span>
                                </h5>
                                <p class="mb-0 text-sm">{{ $card['label'] }}</p>
                            </div>
                            <div class="icon icon-shape bg-gradient-primary shadow border-radius-md">
                                <img class="w-100" src="{{ asset('assets/images/icon.svg') }}" />
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach



    </div>

    <hr class="my-4">

    <div class="container">
        <h2>Welcome, {{ $user->name }} ({{ ucfirst($user->role) }})</h2>

        @if ($user->role === 'admin')
            <div class="row my-3">
                <div>Total Sales Today: {{ $todaySales ?? 0 }}</div>
                <div>Total Sales This Month: {{ $monthSales ?? 0 }}</div>
                <div>Total Sales Overall: {{ $totalSales ?? 0 }}</div>
            </div>

            <h3>Recent Orders</h3>
            <ul>
                @foreach ($recentOrders as $order)
                    <li>Order #{{ $order->id }} - {{ $order->created_at->format('d M Y') }}</li>
                @endforeach
            </ul>

            <h3>Recent Invoices</h3>
            <ul>
                @foreach ($recentInvoices as $invoice)
                    <li>Invoice #{{ $invoice->id }} - Amount: {{ $invoice->total_amount }}</li>
                @endforeach
            </ul>
        @else
            <div class="row my-3">
                <div>Total Orders: {{ $totalOrders ?? 0 }}</div>
                <div>Total Invoices: {{ $totalInvoices ?? 0 }}</div>
                <div>Total Spent: {{ $totalSpent ?? 0 }}</div>
            </div>

            <h3>Your Recent Orders</h3>
            <ul>
                @foreach ($orders as $order)
                    <li>Order #{{ $order->id }} - {{ $order->created_at->format('d M Y') }}</li>
                @endforeach
            </ul>

            <h3>Your Recent Invoices</h3>
            <ul>
                @foreach ($invoices as $invoice)
                    <li>Invoice #{{ $invoice->id }} - Amount: {{ $invoice->total_amount }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const role = "{{ $user->role }}";

            if (role === 'admin') {
                axios.get('{{ route('api.invoices.stats') }}')
                    .then(res => {
                        const data = res.data;
                        document.getElementById('invoice').textContent = data.total_invoices ?? 0;
                        document.getElementById('total').textContent = data.total_amount ?? 0;
                        // document.getElementById('vat').textContent = data.vat ?? 0;
                        // document.getElementById('payable').textContent = data.payable ?? 0;
                    });

                axios.get('{{ route('api.orders.stats') }}')
                    .then(res => {
                        const data = res.data;
                        document.getElementById('orders').textContent = data.total_orders ?? 0;
                        document.getElementById('product').textContent = data.total_products ?? 0;
                        document.getElementById('category').textContent = data.total_categories ?? 0;
                        document.getElementById('customer').textContent = data.total_customers ?? 0;
                    });
            } else {
                axios.get('{{ route('api.invoices.stats') }}')
                    .then(res => {
                        document.getElementById('my-invoices').textContent = res.data.total_invoices ?? 0;
                    });

                axios.get('{{ route('api.orders.stats') }}')
                    .then(res => {
                        document.getElementById('my-orders').textContent = res.data.total_orders ?? 0;
                    });
            }
        });
    </script>
@endpush
