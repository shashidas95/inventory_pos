@extends('layouts.sidenav-layout')

@section('content')
    <div class="container mt-4">

        <h2 class="mb-4">Sales / Invoices</h2>

        <table class="table table-bordered table-striped">
            <thead class="thead-light">
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->user->name ?? 'Walk-in' }}</td>
                        <td>${{ number_format($invoice->total_amount, 2) }}</td>
                        <td>{{ ucfirst($invoice->status) }}</td>
                        <td>{{ $invoice->invoice_date->format('d-m-Y') }}</td>
                        <td>
                            <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-sm btn-primary">View</a>
                            <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank"
                                class="btn btn-sm btn-success">Print</a>
                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>

    </div>
@endsection
