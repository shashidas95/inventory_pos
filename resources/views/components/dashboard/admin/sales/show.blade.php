@extends('layouts.sidenav-layout')

@section('content')
<div class="container mt-4">
    {{-- Letterhead / Store Info --}}
    <div class="text-center mb-4">
        <h3>{{ $invoice->store()->name() }}</h3>
        <p>Address: 123, Street Name, City</p>
        <p>Phone: +880123456789 | Email: store@example.com</p>
    </div>

    <h2 class="mb-4">Invoice Details</h2>

    <div class="card mb-4">
        <div class="card-header">
            Invoice #{{ $invoice->invoice_number }}
        </div>
        <div class="card-body">
            <p><strong>Customer:</strong> {{ $invoice->customer->name ?? 'Walk-in' }}</p>
            <p><strong>Date:</strong> {{ $invoice->invoice_date->format('d-m-Y') }}</p>
            <p><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>
            <p><strong>Total Amount:</strong> ${{ number_format($invoice->total_amount, 2) }}</p>
        </div>
    </div>

    <h4>Products</h4>
    <table class="table table-bordered">
        <thead class="thead-light">
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->invoiceDetails as $detail)
                <tr>
                    <td>{{ $detail->product->name }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>${{ number_format($detail->amount, 2) }}</td>
                    <td>${{ number_format($detail->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="btn btn-success mt-3">Print Invoice</a>
    <a href="{{ route('sales.list') }}" class="btn btn-secondary mt-3">Back to List</a>
</div>
@endsection
