<div class="container mt-5 mb-5">
    <h2 class="mb-4 text-center">Create Sale / Invoice</h2>

    <form id="create-sale-form">
        {{-- Store  --}}
        <div class="mb-4">
            <label for="customer" class="form-label fw-bold">Select Store:</label>
            <select id="store_id" class="form-control">
                <option value="">-- Select Store --</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Customer --}}
        <div class="mb-4">
            <label for="customer" class="form-label fw-bold">Select Customer:</label>
            <select id="customer" name="customer_id" class="form-select form-select-lg" required>
                <option value="">-- Select Customer --</option>
                @foreach ($customers as $customer)
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
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td class="price fw-bold text-end">0.00</td>
                        <td><input type="number" name="products[0][qty]" class="form-control quantity" min="1"
                                value="1" required></td>
                        <td class="subtotal fw-bold text-end">0.00</td>
                        <td><button type="button" class="btn btn-outline-danger btn-sm remove-item">Remove</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button type="button" id="add-item" class="btn btn-primary mb-4">Add Product</button>

        {{-- VAT & Discount Inputs --}}
        <div class="card shadow-sm p-4 mb-4" style="max-width: 400px;">
            <h5 class="mb-3 text-center">Invoice Summary</h5>
            <div class="d-flex justify-content-between mb-2">
                <span>Total:</span> <span id="total" class="fw-bold">$0.00</span>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <span>VAT (%):</span>
                <input type="number" id="vat-input" class="form-control w-50 text-end" value="5" min="0"
                    max="100">
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>VAT ($):</span> <span id="vat" class="fw-bold">$0.00</span>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <span>Discount ($):</span>
                <input type="number" id="discount-input" class="form-control w-50 text-end" value="0"
                    min="0">
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Discount Applied:</span> <span id="discount" class="fw-bold">$0.00</span>
            </div>

            <div class="d-flex justify-content-between mt-3 p-2 bg-success text-white rounded fw-bold fs-5">
                <span>Payable:</span> <span id="payable">$0.00</span>
            </div>
        </div>

    </form>
</div>

<div class="fixed-bottom bg-white p-3 shadow d-flex justify-content-center">
    <button type="button" id="confirm-sale" class="btn btn-success btn-lg w-50">Confirm Sale</button>
</div>

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const itemsContainer = document.getElementById('items-container');
            const totalEl = document.getElementById('total');
            const vatEl = document.getElementById('vat');
            const discountEl = document.getElementById('discount');
            const payableEl = document.getElementById('payable');
            const vatInput = document.getElementById('vat-input');
            const discountInput = document.getElementById('discount-input');
            const addItemBtn = document.getElementById('add-item');
            const storeSelect = document.getElementById('store_id');

            function updateRowSubtotal(row) {
                const productSelect = row.querySelector('select.product');
                const qtyInput = row.querySelector('input.quantity');
                const priceCell = row.querySelector('.price');
                const subtotalCell = row.querySelector('.subtotal');

                const price = productSelect.value ? parseFloat(productSelect.selectedOptions[0].dataset.price) : 0;
                const qty = parseInt(qtyInput.value) || 0;
                const subtotal = price * qty;

                priceCell.innerText = price.toFixed(2);
                subtotalCell.innerText = subtotal.toFixed(2);

                return subtotal;
            }

            function updateTotals() {
                let total = 0;
                itemsContainer.querySelectorAll('.item').forEach(row => {
                    total += updateRowSubtotal(row);
                });

                const vatPercent = parseFloat(vatInput.value) || 0;
                const discountValue = parseFloat(discountInput.value) || 0;
                const vatAmount = total * vatPercent / 100;
                const payable = total + vatAmount - discountValue;

                totalEl.innerText = '$' + total.toFixed(2);
                vatEl.innerText = '$' + vatAmount.toFixed(2);
                discountEl.innerText = '$' + discountValue.toFixed(2);
                payableEl.innerText = '$' + payable.toFixed(2);
            }

            // Update totals on input change
            itemsContainer.addEventListener('input', updateTotals);
            itemsContainer.addEventListener('change', updateTotals);
            vatInput.addEventListener('input', updateTotals);
            discountInput.addEventListener('input', updateTotals);

            // Add new row
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

                itemsContainer.appendChild(newRow);
                updateTotals();
            });

            // Remove row
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

            // Confirm Sale
            document.getElementById('confirm-sale').addEventListener('click', async function() {
                const customerId = document.getElementById('customer').value;
                const storeId = storeSelect.value;

                if (!customerId) {
                    alert('Please select a customer.');
                    return;
                }

                if (!storeId) {
                    alert('Please select a store.');
                    return;
                }

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

                if (products.length === 0) {
                    alert('Please add at least one product.');
                    return;
                }

                const vatPercentage = parseFloat(vatInput.value) || 0;
                const discountAmount = parseFloat(discountInput.value) || 0;

                try {
                    const res = await axios.post('{{ route('invoice.store.sale') }}', {
                        customer_id: customerId,
                        store_id: storeId,
                        products: products,
                        vat: vatPercentage,
                        discount: discountAmount
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

    @media (max-width: 576px) {

        #products-table th,
        #products-table td {
            font-size: 0.9rem;
            padding: 0.3rem;
        }

        #confirm-sale {
            width: 90% !important;
        }
    }
</style>
