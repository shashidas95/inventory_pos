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
                    <th>Total Amount</th>
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
                        <td>{{ $invoice->total_amount }}</td>
                        <td>
                            <a href="{{ route('invoices.print', $invoice->id) }}" class="btn btn-sm btn-primary">Print</a>
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
