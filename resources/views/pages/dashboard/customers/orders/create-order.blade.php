@extends('layouts.sidenav-layout')

@section('content')
    <div class="container-fluid mt-5 mb-5">
        <div class="row">
            {{-- Left: Order Form --}}
            <div class="col-lg-8 mb-4">
                <h2 class="mb-4 text-center">Create Sale / Invoice</h2>

                <form id="create-sale-form">
                    {{-- Customer --}}
                    <div class="mb-4">
                        <label for="customer" class="form-label fw-bold">Select Customer:</label>
                        <select id="customer" name="customer_id" class="form-select form-select-lg" required>
                            <option value="">-- Select Customer --</option>
                            @foreach (\App\Models\User::where('role', 'customer')->get() as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Products Table --}}
                    <div class="table-responsive shadow-sm rounded mb-4">
                        <table class="table table-hover align-middle" id="products-table">
                            <thead class="table-dark text-white">
                                <tr>
                                    <th>Product</th>
                                    <th>Price ($)</th>
                                    <th>Available Stock</th>
                                    <th>Quantity</th>
                                    <th>Subtotal ($)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="items-container">
                                <tr class="item">
                                    <td>
                                        <select name="products[0][product_id]" class="form-select product" required>
                                            <option value="">-- Select Product --</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                                    data-stock="{{ $product->stock }}">
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="price fw-bold text-end">0.00</td>
                                    <td class="stock fw-bold text-center">0</td>
                                    <td><input type="number" name="products[0][qty]" class="form-control quantity"
                                            min="1" value="1" required></td>
                                    <td class="subtotal fw-bold text-end">0.00</td>
                                    <td><button type="button"
                                            class="btn btn-outline-danger btn-sm remove-item">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <button type="button" id="add-item" class="btn btn-primary mb-4">Add Product</button>
                </form>
            </div>

            {{-- Right: Cart Summary --}}
            <div class="col-lg-4">
                <div class="card shadow-sm p-4 sticky-top" style="top: 20px;">
                    <h4 class="text-center mb-4">Cart Summary</h4>

                    <ul class="list-group mb-3" id="cart-summary">
                        {{-- Live product list will appear here --}}
                    </ul>

                    <div class="mb-2 d-flex justify-content-between">
                        <span>Total:</span> <span id="total" class="fw-bold">$0.00</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>VAT (%):</span>
                        <input type="number" id="vat-input" class="form-control w-50 text-end" value="5"
                            min="0" max="100">
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>VAT ($):</span> <span id="vat" class="fw-bold">$0.00</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Discount ($):</span>
                        <input type="number" id="discount-input" class="form-control w-50 text-end" value="0"
                            min="0">
                    </div>
                    <div class="mb-3 d-flex justify-content-between">
                        <span>Discount Applied:</span> <span id="discount" class="fw-bold">$0.00</span>
                    </div>

                    <div class="d-flex justify-content-between mt-3 p-3 bg-success text-white rounded fw-bold fs-5">
                        <span>Payable:</span> <span id="payable">$0.00</span>
                    </div>

                    <button type="button" id="confirm-sale" class="btn btn-success btn-lg w-100 mt-3">Confirm Sale</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const itemsContainer = document.getElementById('items-container');
            const cartSummary = document.getElementById('cart-summary');
            const totalEl = document.getElementById('total');
            const vatEl = document.getElementById('vat');
            const discountEl = document.getElementById('discount');
            const payableEl = document.getElementById('payable');
            const vatInput = document.getElementById('vat-input');
            const discountInput = document.getElementById('discount-input');
            const addItemBtn = document.getElementById('add-item');

            function updateRowSubtotal(row) {
                const productSelect = row.querySelector('select.product');
                const qtyInput = row.querySelector('input.quantity');
                const priceCell = row.querySelector('.price');
                const subtotalCell = row.querySelector('.subtotal');
                const stockCell = row.querySelector('.stock');

                const price = productSelect.value ? parseFloat(productSelect.selectedOptions[0].dataset.price) : 0;
                const stock = productSelect.value ? parseInt(productSelect.selectedOptions[0].dataset.stock) : 0;
                let qty = parseInt(qtyInput.value) || 0;

                if (qty > stock) {
                    alert(`Maximum available stock: ${stock}`);
                    qtyInput.value = stock;
                    qty = stock;
                }

                const subtotal = price * qty;
                priceCell.innerText = price.toFixed(2);
                subtotalCell.innerText = subtotal.toFixed(2);
                stockCell.innerText = stock;

                return {
                    productName: productSelect.selectedOptions[0]?.text || '-',
                    qty,
                    subtotal
                };
            }

            function updateCartSummary() {
                cartSummary.innerHTML = '';
                itemsContainer.querySelectorAll('.item').forEach(row => {
                    const data = updateRowSubtotal(row);
                    if (data.qty > 0) {
                        const li = document.createElement('li');
                        li.className = 'list-group-item d-flex justify-content-between align-items-center';
                        li.innerHTML =
                            `<span>${data.productName} x ${data.qty}</span> <span>$${data.subtotal.toFixed(2)}</span>`;
                        cartSummary.appendChild(li);
                    }
                });
            }

            function updateTotals() {
                let total = 0;
                itemsContainer.querySelectorAll('.item').forEach(row => total += updateRowSubtotal(row).subtotal);

                const vatPercent = parseFloat(vatInput.value) || 0;
                const discountValue = parseFloat(discountInput.value) || 0;
                const vatAmount = total * vatPercent / 100;
                const payable = total + vatAmount - discountValue;

                totalEl.innerText = '$' + total.toFixed(2);
                vatEl.innerText = '$' + vatAmount.toFixed(2);
                discountEl.innerText = '$' + discountValue.toFixed(2);
                payableEl.innerText = '$' + payable.toFixed(2);

                updateCartSummary();
            }

            addItemBtn.addEventListener('click', function() {
                const index = itemsContainer.querySelectorAll('.item').length;
                const newRow = itemsContainer.querySelector('.item').cloneNode(true);

                const select = newRow.querySelector('select.product');
                select.value = '';
                select.name = `products[${index}][product_id]`;

                const qty = newRow.querySelector('input.quantity');
                qty.value = 1;
                qty.name = `products[${index}][qty]`;

                newRow.querySelector('.price').innerText = '0.00';
                newRow.querySelector('.subtotal').innerText = '0.00';
                newRow.querySelector('.stock').innerText = '0';

                itemsContainer.appendChild(newRow);
                updateTotals();
            });

            itemsContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-item')) {
                    const rows = itemsContainer.querySelectorAll('.item');
                    if (rows.length > 1) {
                        e.target.closest('tr').remove();
                        updateTotals();
                    } else {
                        alert('At least one product is required.');
                    }
                }
            });

            itemsContainer.addEventListener('input', updateTotals);
            itemsContainer.addEventListener('change', updateTotals);
            vatInput.addEventListener('input', updateTotals);
            discountInput.addEventListener('input', updateTotals);

            document.getElementById('confirm-sale').addEventListener('click', async function() {
                const customerId = document.getElementById('customer').value;
                if (!customerId) return alert('Please select a customer.');

                const products = [];
                itemsContainer.querySelectorAll('.item').forEach(row => {
                    const productSelect = row.querySelector('select.product');
                    const qtyInput = row.querySelector('input.quantity');
                    if (productSelect.value && qtyInput.value > 0) {
                        products.push({
                            product_id: productSelect.value,
                            qty: parseInt(qtyInput.value),
                            price: parseFloat(productSelect.selectedOptions[0].dataset
                                .price)
                        });
                    }
                });

                if (products.length === 0) return alert('Please add at least one product.');

                try {
                    const res = await axios.post('{{ route('invoice.store.sale') }}', {
                        customer_id: customerId,
                        products,
                        vat: parseFloat(vatInput.value) || 0,
                        discount: parseFloat(discountInput.value) || 0
                    });

                    if (res.data.invoice_id) {
                        alert('Sale created successfully!');
                        window.location.href = `/backend/admin/invoices/show/${res.data.invoice_id}`;
                    }
                } catch (err) {
                    console.error(err);
                    alert('Error creating sale: ' + (err.response?.data?.message || err.message));
                }
            });

            updateTotals();
        });
    </script>
@endpush

<style>
    #products-table .item:hover {
        background-color: #f8f9fa;
    }

    #products-table td,
    #products-table th {
        vertical-align: middle;
    }

    @media (max-width: 992px) {
        .sticky-top {
            position: static !important;
            margin-top: 20px;
        }
    }
</style>
