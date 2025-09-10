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
                        'id' => 'today-sales',
                        'label' => "Today's Sale",
                        'currency' => true,
                        'value' => $todaySales,
                        'url' => route('api.sales.stats'),
                    ],
                    [
                        'id' => 'month-sales',
                        'label' => "This Month's Sale",
                        'currency' => true,
                        'value' => $monthSales,
                        'url' => route('api.sales.stats'),
                    ],
                    [
                        'id' => 'total-sales',
                        'label' => 'Total Sale',
                        'currency' => true,
                        'value' => $totalSales,
                        'url' => route('api.sales.stats'),
                    ],
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
            <h3>Recent Orders</h3>
            <ul>
                @foreach ($recentOrders as $order)
                    <li>Order #{{ $order->id }} - {{ $order->created_at->format('d M Y') }}</li>
                @endforeach
            </ul>

            <h3>Recent Invoices</h3>
            <ul>
                @foreach ($recentInvoices as $invoice)
                    <li>Invoice #{{ $invoice->id }} - Amount: {{ $invoice->final_total ?? $invoice->total_amount }}
                    </li>
                @endforeach
            </ul>
        @else
            <h3>Your Recent Orders</h3>
            <ul>
                @foreach ($orders as $order)
                    <li>Order #{{ $order->id }} - {{ $order->created_at->format('d M Y') }}</li>
                @endforeach
            </ul>

            <h3>Your Recent Invoices</h3>
            <ul>
                @foreach ($invoices as $invoice)
                    <li>Invoice #{{ $invoice->id }} - Amount: {{ $invoice->final_total ?? $invoice->total_amount }}
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const role = "{{ $user->role }}";

            if (role === 'admin') {
                // Fetch overall stats (products, categories, orders, invoices)
                axios.get('{{ route('api.orders.stats') }}')
                    .then(res => {
                        const data = res.data;
                        document.getElementById('orders').textContent = data.total_orders ?? 0;
                        document.getElementById('product').textContent = data.total_products ?? 0;
                        document.getElementById('category').textContent = data.total_categories ?? 0;
                        if (document.getElementById('customer'))
                            document.getElementById('customer').textContent = data.total_customers ?? 0;
                    });

                axios.get('{{ route('api.invoices.stats') }}')
                    .then(res => {
                        const data = res.data;
                        document.getElementById('invoice').textContent = data.total_invoices ?? 0;
                    });

                // Fetch sales-specific stats (Today, Month, Total) live
                axios.get('{{ route('api.sales.stats') }}')
                    .then(res => {
                        const data = res.data;
                        document.getElementById('today-sales').textContent = data.todaySales ?? 0;
                        document.getElementById('month-sales').textContent = data.monthSales ?? 0;
                        document.getElementById('total-sales').textContent = data.totalSales ?? 0;
                    })
                    .catch(err => console.error("Sales stats error:", err));

            } else {
                // Customer view
                axios.get('{{ route('api.invoices.stats') }}')
                    .then(res => document.getElementById('my-invoices').textContent = res.data.total_invoices ?? 0);

                axios.get('{{ route('api.orders.stats') }}')
                    .then(res => document.getElementById('my-orders').textContent = res.data.total_orders ?? 0);
            }
        });
    </script>
@endpush
