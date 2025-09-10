@extends('layouts.sidenav-layout')

@section('content')
    <div class="container bg-white p-4 shadow-sm mt-5">

        {{-- 🏬 Store Info / Letterhead --}}
        <div class="text-center mb-4">
            @if (optional($store)->logo)
                <img src="{{ asset('storage/' . $store->logo) }}" alt="Store Logo" class="mb-2" style="height: 80px;">
            @endif
            <h2 class="fw-bold">{{ optional($store)->name ?? 'All Stores' }}</h2>
            <p class="small mb-0">
                Address: {{ optional($store)->address ?? '123, Street Name, City' }} <br>
                Phone: {{ optional($store)->phone ?? '+880123456789' }} |
                Email: {{ optional($store)->email ?? 'store@example.com' }}
            </p>
        </div>

        {{-- 📋 Report Header + Store Filter --}}
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <h4 class="fw-semibold">Stock Report</h4>
            <div class="d-flex gap-2">
                <form method="GET" action="{{ route('stock.report') }}">
                    <select name="store_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Stores</option>
                        @foreach ($stores as $s)
                            <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
                <a href="#" onclick="window.print()" class="btn btn-success no-print">Print</a>
            </div>
        </div>

        {{-- 📦 Stock Table --}}
        @if ($storeId)
            {{-- Single Store --}}
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>SKU</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th class="text-end">Price ($)</th>
                            <th class="text-end">Quantity Available</th>
                            <th class="text-end">Stock Value ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @foreach ($products as $i => $product)
                            @php
                                $qty = $product->pivot->quantity ?? 0;
                                $value = $qty * $product->price;
                                $total += $value;
                            @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>-</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category->name ?? '-' }}</td>
                                <td>{{ $product->unit ?? 'pcs' }}</td>
                                <td class="text-end">{{ number_format($product->price, 2) }}</td>
                                <td class="text-end">{{ $qty }}</td>
                                <td class="text-end">{{ number_format($value, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr class="table-secondary">
                            <td colspan="7" class="text-end">Total Stock Value ({{ $store->name }})</td>
                            <td class="text-end">${{ number_format($total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            {{-- All Stores --}}
            {{-- Summary Totals Table --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th class="text-end">Total Quantity</th>
                            <th class="text-end">Stock Value ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; @endphp
                        @foreach ($products as $i => $product)
                            @php
                                $totalQty = $stores->sum(
                                    fn($s) => $s->products->find($product->id)->pivot->quantity ?? 0,
                                );
                                $totalValue = $stores->sum(
                                    fn($s) => ($s->products->find($product->id)->pivot->quantity ?? 0) *
                                        $product->price,
                                );
                                $grandTotal += $totalValue;
                            @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $product->name }}</td>
                                <td class="text-end">{{ $totalQty }}</td>
                                <td class="text-end">{{ number_format($totalValue, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr class="table-dark">
                            <td colspan="3" class="text-end">Grand Total (All Stores)</td>
                            <td class="text-end">${{ number_format($grandTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Collapsible Per-Store Breakdown --}}
            @foreach ($stores as $s)
                <div class="mt-4">
                    <h4>{{ $s->name }}</h4>
                    <p>
                        Address: {{ $s->address }} <br>
                        Phone: {{ $s->phone }} | Email: {{ $s->email }}
                    </p>

                    <button class="btn btn-sm btn-outline-primary mb-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#store-{{ $s->id }}" aria-expanded="false">
                        Toggle Stock Report
                    </button>

                    <div class="collapse" id="store-{{ $s->id }}">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>SKU</th>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Unit</th>
                                        <th class="text-end">Price ($)</th>
                                        <th class="text-end">Quantity Available</th>
                                        <th class="text-end">Stock Value ($)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $storeTotal = 0; @endphp
                                    @foreach ($s->products as $i => $product)
                                        @php
                                            $qty = $product->pivot->quantity ?? 0;
                                            $value = $qty * $product->price;
                                            $storeTotal += $value;
                                        @endphp
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>-</td>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->category->name ?? '-' }}</td>
                                            <td>{{ $product->unit ?? 'pcs' }}</td>
                                            <td class="text-end">{{ number_format($product->price, 2) }}</td>
                                            <td class="text-end">{{ $qty }}</td>
                                            <td class="text-end">{{ number_format($value, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="fw-bold">
                                    <tr class="table-secondary">
                                        <td colspan="7" class="text-end">Total Stock Value ({{ $s->name }})</td>
                                        <td class="text-end">${{ number_format($storeTotal, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection
