@extends('layouts.sidenav-layout')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Stock Report</h3>
        <a href="#" onclick="window.print()" class="btn btn-success no-print">Print Stock Report</a>
    </div>

    <table class="table table-striped table-bordered" id="stock-table">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>SKU</th>
                <th>Product</th>
                <th>Category</th>
                <th>Unit</th>
                <th>Price ($)</th>
                <th>Quantity Available</th>
                <th>Stock Value ($)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr @if($product->quantity <= $product->reorder_level) class="table-danger" @endif>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $product->sku ?? '-' }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->unit ?? 'pcs' }}</td>
                    <td>{{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->quantity }}</td>
                    <td>{{ number_format($product->quantity * $product->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="fw-bold">
            <tr>
                <td colspan="6" class="text-end">Total Stock Value</td>
                <td colspan="2">
                    ${{ number_format($products->sum(fn($p) => $p->quantity * $p->price), 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>

@push('script')
<script>
    $(document).ready(function() {
        $('#stock-table').DataTable({
            pageLength: 25,
            order: [[1, 'asc']]
        });
    });
</script>
@endpush
@endsection
