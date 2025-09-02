<div class="container-fluid mt-3">
    <div class="row">

        <!-- Left: Scrollable Products -->
        <div class="col-md-8">
            <h4>Products</h4>
            <div class="row overflow-auto" style="max-height: 80vh;">
                @foreach ($products as $product)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="/storage/{{ $product->image }}" class="card-img-top" alt="{{ $product->name }}"
                                height="150">
                            <div class="card-body text-center">
                                <h5>{{ $product->name }}</h5>
                                <p class="fw-bold">${{ number_format($product->price, 2) }}</p>
                                <button class="btn btn-success btn-sm"
                                    onclick="orderNow('{{ $product->id }}', '{{ $product->name }}', {{ $product->price }})">
                                    Order Now
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right: Live Cart Sidebar -->
        <div class="col-md-4">
            <h4>Cart</h4>
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="items-container"></tbody>
                    </table>

                    <div class="mb-2">
                        <label>VAT (%)</label>
                        <input type="number" id="vat-input" value="5" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label>Discount ($)</label>
                        <input type="number" id="discount-input" value="0" class="form-control">
                    </div>

                    <div class="mb-2">
                        <p>Total: <span id="total">$0.00</span></p>
                        <p>VAT: <span id="vat">$0.00</span></p>
                        <p>Discount: <span id="discount">$0.00</span></p>
                        <p><strong>Payable: <span id="payable">$0.00</span></strong></p>
                    </div>

                    <button id="confirm-sale" class="btn btn-primary w-100">Confirm Sale</button>
                </div>
            </div>
        </div>

    </div>
</div>

<input type="hidden" id="customer-id" value="{{ auth()->id() }}">


@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cart = [];
            const itemsContainer = document.getElementById('items-container');
            const totalEl = document.getElementById('total');
            const vatEl = document.getElementById('vat');
            const discountEl = document.getElementById('discount');
            const payableEl = document.getElementById('payable');
            const vatInput = document.getElementById('vat-input');
            const discountInput = document.getElementById('discount-input');
            const customerId = document.getElementById('customer-id').value;
            const token = localStorage.getItem('jwt_token');

            // Add product to cart
            window.orderNow = function(productID, productName, productPrice) {
                const existing = cart.find(p => p.product_id == productID);
                if (existing) existing.qty += 1;
                else cart.push({
                    product_id: productID,
                    name: productName,
                    price: parseFloat(productPrice),
                    qty: 1
                });
                renderCart();
            }

            // Render cart items
            function renderCart() {
                itemsContainer.innerHTML = '';
                cart.forEach((item, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                <td>${item.name}</td>
                <td class="text-end">${item.price.toFixed(2)}</td>
                <td><input type="number" min="1" value="${item.qty}" class="form-control quantity" data-index="${index}"></td>
                <td class="text-end">${(item.price * item.qty).toFixed(2)}</td>
                <td><button class="btn btn-sm btn-outline-danger remove-item" data-index="${index}">Remove</button></td>
            `;
                    itemsContainer.appendChild(row);
                });
                updateTotals();
            }

            // Update totals
            function updateTotals() {
                let total = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
                const vat = parseFloat(vatInput.value) || 0;
                const discount = parseFloat(discountInput.value) || 0;
                const vatAmount = total * vat / 100;
                const payable = total + vatAmount - discount;

                totalEl.innerText = '$' + total.toFixed(2);
                vatEl.innerText = '$' + vatAmount.toFixed(2);
                discountEl.innerText = '$' + discount.toFixed(2);
                payableEl.innerText = '$' + payable.toFixed(2);
            }

            // Quantity change
            itemsContainer.addEventListener('input', function(e) {
                if (e.target.classList.contains('quantity')) {
                    const index = e.target.dataset.index;
                    cart[index].qty = parseInt(e.target.value) || 1;
                    renderCart();
                }
            });

            // Remove item
            itemsContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-item')) {
                    const index = e.target.dataset.index;
                    cart.splice(index, 1);
                    renderCart();
                }
            });

            vatInput.addEventListener('input', updateTotals);
            discountInput.addEventListener('input', updateTotals);

            // Confirm sale
            document.getElementById('confirm-sale').addEventListener('click', async function() {
                if (cart.length === 0) return alert('Cart is empty!');
                const vat = parseFloat(vatInput.value) || 0;
                const discount = parseFloat(discountInput.value) || 0;

                try {
                    await axios.post('{{ route('customer.order.store') }}', {
                        products: cart.map(p => ({
                            product_id: p.product_id,
                            qty: p.qty
                        })),
                        vat,
                        discount
                    }, {
                        headers: {
                            'Authorization': 'Bearer ' + token
                        }
                    });

                    alert('Order placed successfully!');
                    cart.length = 0;
                    renderCart();
                } catch (err) {
                    console.error(err.response?.data || err);
                    alert('Error creating order. Check console.');
                }
            });

            renderCart();
        });
    </script>
@endpush



{{--
<div class="container mt-4">
    <h3>Products</h3>
    <div class="row">
        @foreach ($products as $product)
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="card-text fw-bold">${{ number_format($product->price, 2) }}</p>
                        <button class="btn btn-success btn-sm order-now" data-id="{{ $product->id }}"
                            data-name="{{ $product->name }}" data-price="{{ $product->price }}">
                            Order Now
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>


@push('script')
    <script>
        // Load cart from localStorage on page load
        let cart = JSON.parse(localStorage.getItem('posCart')) || [];
        const itemsContainer = document.getElementById('items-container');

        function loadCart() {
            itemsContainer.innerHTML = ''; // clear existing rows

            cart.forEach((item, index) => {
                const row = document.createElement('tr');
                row.classList.add('item');
                row.innerHTML = `
            <td>
                <input type="text" class="form-control" value="${item.name}" disabled>
                <input type="hidden" name="products[${index}][product_id]" value="${item.id}">
            </td>
            <td class="price fw-bold text-end">${item.price.toFixed(2)}</td>
            <td>
                <input type="number" name="products[${index}][qty]" class="form-control quantity" value="${item.qty}" min="1" required>
            </td>
            <td class="subtotal fw-bold text-end">${(item.price * item.qty).toFixed(2)}</td>
            <td>
                <button type="button" class="btn btn-outline-danger btn-sm remove-item">Remove</button>
            </td>
        `;
                itemsContainer.appendChild(row);
            });

            updateTotals(); // recalc totals
        }

        loadCart();

        // Update localStorage when quantity changes or row removed
        itemsContainer.addEventListener('input', function(e) {
            if (e.target.classList.contains('quantity')) {
                cart[e.target.closest('tr').rowIndex - 1].qty = parseInt(e.target.value);
                localStorage.setItem('posCart', JSON.stringify(cart));
                updateTotals();
            }
        });

        itemsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item')) {
                const rowIndex = e.target.closest('tr').rowIndex - 1;
                cart.splice(rowIndex, 1);
                localStorage.setItem('posCart', JSON.stringify(cart));
                e.target.closest('tr').remove();
                updateTotals();
            }
        });

        // Clear cart after successful order
        document.getElementById('confirm-sale').addEventListener('click', async function() {
            if (cart.length === 0) {
                alert('Cart is empty!');
                return;
            }

            const customerId = document.getElementById('customer').value;
            const vat = parseFloat(document.getElementById('vat-input').value) || 0;
            const discount = parseFloat(document.getElementById('discount-input').value) || 0;

            try {
                const res = await axios.post('{{ route('customer.order.store') }}', {
                    customer_id: customerId,
                    products: cart.map(p => ({
                        product_id: p.id,
                        qty: p.qty
                    })),
                    vat: vat,
                    discount: discount
                });

                if (res.data.order_id) {
                    alert('Order created successfully!');
                    localStorage.removeItem('posCart'); // clear cart
                    window.location.href = `/customer/orders/list`;
                }
            } catch (err) {
                console.error(err);
                alert('Error creating order: ' + (err.response?.data?.message || err.message));
            }
        });
    </script>
@endpush --}}
