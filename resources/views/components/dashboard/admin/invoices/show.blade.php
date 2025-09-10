<div class="container bg-white p-4 shadow-sm">

    {{-- 🏬 Store Info / Letterhead --}}
    <div class="text-center mb-4">
        @if (optional($invoice->store)->logo)
            <img src="{{ asset('storage/' . $invoice->store->logo) }}" alt="Store Logo" class="mb-2"
                style="height: 80px;">
        @endif
        <h2 class="fw-bold">{{ optional($invoice->store)->name ?? 'My Store Name' }}</h2>
        <p class="small mb-0">
            Address: {{ optional($invoice->store)->address ?? '123, Street Name, City' }} <br>
            Phone: {{ optional($invoice->store)->phone ?? '+880123456789' }} |
            Email: {{ optional($invoice->store)->email ?? 'store@example.com' }}
        </p>
    </div>

    {{-- 📋 Invoice Info --}}
    <div class="mb-4 border-bottom pb-3">
        <h4 class="fw-semibold">Invoice #{{ $invoice->invoice_number }}</h4>
        <p><strong>Customer:</strong> {{ $invoice->customer->name ?? 'Walk-in' }}</p>
        <p><strong>Created By:</strong> {{ $invoice->user->name ?? 'Admin' }}</p>
        <p><strong>Date:</strong> {{ $invoice->invoice_date->format('d-m-Y') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>
    </div>

    {{-- 📦 Products Table --}}
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Product</th>
                    <th scope="col" class="text-center">Quantity</th>
                    <th scope="col" class="text-end">Unit Price ($)</th>
                    <th scope="col" class="text-end">Total ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->invoiceDetails as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detail->product->name ?? 'N/A' }}</td>
                        <td class="text-center">{{ $detail->quantity }}</td>
                        <td class="text-end">{{ number_format($detail->amount, 2) }}</td>
                        <td class="text-end">{{ number_format($detail->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- 💰 Totals --}}
    <div class="row mt-4">
        <div class="col-md-6"></div>
        <div class="col-md-6 text-end">
            <p>Subtotal: ${{ number_format($invoice->subtotal_amount, 2) }}</p>
            <p>VAT ({{ $invoice->vat_percentage }}%): ${{ number_format($invoice->vat_amount, 2) }}</p>
            <p>Discount: ${{ number_format($invoice->discount_amount, 2) }}</p>
            <h5 class="fw-bold mt-2">Total Payable: ${{ number_format($invoice->final_total, 2) }}</h5>
        </div>
    </div>

    {{-- 🖨️ Print Button --}}
    <div class="mt-4 text-end">
        <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="btn btn-primary">
            Print Invoice
        </a>
       
    </div>

</div>
