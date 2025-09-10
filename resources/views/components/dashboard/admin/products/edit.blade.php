<div class="container mt-4">
    <div class="card shadow-sm p-4">
        <h3>Edit Product</h3>
        <hr>

        <div id="alertMessage"></div>

        <form id="productForm" enctype="multipart/form-data" class="row g-3" method="">
            @csrf
            @method('PUT')

            <!-- Product Image -->
            <div class="col-md-4 text-center">
                <label class="form-label">Product Image</label>
                <div>
                    <img id="productImage"
                        src="{{ $product->image ? asset('storage/' . $product->image) : asset('assets/images/no-image.png') }}"
                        class="img-fluid rounded border" style="height:120px; width:120px; object-fit:cover;">
                </div>
                <input id="imageFile" type="file" accept="image/*" class="form-control mt-2"
                    onchange="previewImage(event)">
            </div>

            <!-- Product Info -->
            <div class="col-md-8">
                <label class="form-label">Product Name</label>
                <input id="name" name="name" type="text" class="form-control" value="{{ $product->name }}"
                    required>

                <label class="form-label mt-2">Category</label>
                <select id="category_id" name="category_id" class="form-select" required></select>

                <label class="form-label mt-2">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3">{{ $product->description }}</textarea>

                <div class="row mt-2">
                    <div class="col-md-6">
                        <label class="form-label">Price ($)</label>
                        <input id="price" name="price" type="number" min="0" step="0.01"
                            class="form-control" value="{{ $product->price }}" required>

                    </div>
                </div>

                <!-- ✅ Per-store stock -->
                <h5 class="mt-3">Store Stock</h5>
                <div class="row">
                    @foreach ($stores as $store)
                        @php
                            $stock = $product->stores->where('id', $store->id)->first();
                        @endphp
                        <div class="col-md-6 mt-2">
                            <label class="form-label">{{ $store->name }}</label>
                            <input type="number" class="form-control store-qty" data-store-id="{{ $store->id }}"
                                name="stores[{{ $store->id }}]" min="0"
                                value="{{ $stock ? $stock->pivot->quantity : 0 }}">
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-primary mt-3 w-100" onclick="updateProduct()">Update
                    Product</button>
            </div>
        </form>
    </div>
</div>

@push('script')
    <script>
        const productId = "{{ $product->id }}";

        // ✅ Live image preview
        function previewImage(event) {
            let reader = new FileReader();
            reader.onload = () => {
                document.getElementById('productImage').src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        // ✅ Load categories dynamically
        async function loadCategories(selectedId) {
            try {
                const res = await axios.get('/backend/admin/categories/json');
                const select = document.getElementById('category_id');
                select.innerHTML = '';
                res.data.data.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.text = cat.name;
                    if (cat.id == selectedId) option.selected = true;
                    select.appendChild(option);
                });
            } catch (err) {
                console.error(err);
                alert('Failed to load categories.');
            }
        }

        // ✅ Update product
        async function updateProduct() {
            const formData = new FormData(document.getElementById('productForm'));

            // Append image if selected
            const imageFile = document.getElementById('imageFile').files[0];
            if (imageFile) formData.append('image', imageFile);

            try {
                const res = await axios.post(`/backend/admin/products/update/${productId}`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                if (res.data.status === 'success') {
                    alert(res.data.message);
                    window.location.href = '/backend/admin/products/list';
                } else {
                    alert(res.data.message || 'Update failed');
                }
            } catch (err) {
                if (err.response && err.response.status === 422) {
                    // Validation errors
                    let errors = err.response.data.errors;
                    for (let field in errors) {
                        alert(errors[field][0]);
                    }
                } else {
                    console.error(err.response ? err.response.data : err);
                    alert('Unexpected error occurred. Check console.');
                }
            }
        }

        loadCategories("{{ $product->category_id }}");
    </script>
@endpush
