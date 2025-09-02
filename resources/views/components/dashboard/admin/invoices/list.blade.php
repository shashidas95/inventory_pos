@extends('layouts.sidenav-layout')

@section('content')
    <div class="container">
        <h4>Invoices List</h4>
        <table class="table table-bordered" id="invoicesTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Invoice Number</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Subtotal</th>
                    <th>VAT %</th>
                    <th>VAT Amount</th>
                    <th>Discount</th>
                    <th>Final Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->id }}</td>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->customer->name ?? '-' }}</td>
                        <td>{{ $invoice->invoice_date }}</td>
                        <td>{{ number_format($invoice->subtotal_amount, 2) }}</td>
                        <td>{{ $invoice->vat_percentage }}%</td>
                        <td>{{ number_format($invoice->vat_amount, 2) }}</td>
                        <td>{{ number_format($invoice->discount_amount, 2) }}</td>
                        <td>{{ number_format($invoice->final_total, 2) }}</td>
                        <td>
                            <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-sm btn-primary">View</a>
                            <a href="{{ route('invoices.print', $invoice->id) }}" class="btn btn-sm btn-secondary">Print</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @push('script')
        <script>
            $(document).ready(function() {
                $('#invoicesTable').DataTable();
            });
        </script>
    @endpush
@endsection
