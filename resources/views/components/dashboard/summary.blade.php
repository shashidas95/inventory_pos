<div class="container-fluid">
    <div class="row">

        {{-- Dashboard Cards --}}
        @php
            if ($user->role === 'admin' || $user->role === 'manager') {
                // Admin and Manager cards use the same structure
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
                // Customer cards
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

        @if ($user->role === 'admin' || $user->role === 'manager')
            <h3>Recent Orders</h3>
            <ul>
                {{-- Use $recentOrders for admin/manager --}}
                @foreach ($recentOrders as $order)
                    <li>Order #{{ $order->id }} - {{ $order->created_at->format('d M Y') }}</li>
                @endforeach
            </ul>

            <h3>Recent Invoices</h3>
            <ul>
                {{-- Use $recentInvoices for admin/manager --}}
                @foreach ($recentInvoices as $invoice)
                    <li>Invoice #{{ $invoice->id }} - Amount: {{ $invoice->final_total ?? $invoice->total_amount }}
                    </li>
                @endforeach
            </ul>
        @else
            <h3>Your Recent Orders</h3>
            <ul>
                {{-- FIX: Changed $orders to $recentOrders --}}
                @foreach ($recentOrders as $order)
                    <li>Order #{{ $order->id }} - {{ $order->created_at->format('d M Y') }}</li>
                @endforeach
            </ul>

            <h3>Your Recent Invoices</h3>
            <ul>
                {{-- FIX: Changed $invoices to $recentInvoices --}}
                @foreach ($recentInvoices as $invoice)
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

            if (role === 'admin' || role === 'manager') {
                // FIX: Removed the axios calls for static counts (products, categories, orders, invoices)
                // We rely on PHP server-side rendering for these store-scoped initial values.

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
                // FIX: Removed the axios calls for static customer counts for the same reason.
                // We rely on PHP server-side rendering for these initial values.
            }
        });
    </script>
@endpush
