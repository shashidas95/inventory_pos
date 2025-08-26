<!-- edit-product.blade.php -->
<div class="container">
    <div class="card animated fadeIn w-100 p-3">
        <div class="card-body">
            <h4>Edit Product</h4>
            <hr />
            <div id="alertMessage"></div>

            <form id="productForm" enctype="multipart/form-data">
                <div class="row">

                    <!-- Product Image -->
                    <div class="col-md-4 p-2 text-center">
                        <label>Product Image</label>
                        <div>
                            <img id="productImage" src=""
                                style="width:120px;height:120px;border-radius:10px;object-fit:cover;border:1px solid #ddd;">
                        </div>
                        <input id="imageFile" type="file" accept="image/*" class="form-control mt-2" />
                    </div>

                    <!-- Product Info -->
                    <div class="col-md-4 p-2">
                        <label>Product Name</label>
                        <input id="name" class="form-control" type="text" required />
                    </div>

                    <div class="col-md-4 p-2">
                        <label>Category</label>
                        <select id="category_id" class="form-control" required></select>
                    </div>

                    <div class="col-md-6 p-2">
                        <label>Description</label>
                        <textarea id="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="col-md-3 p-2">
                        <label>Quantity</label>
                        <input id="quantity" type="number" min="0" class="form-control" required />
                    </div>

                    <div class="col-md-3 p-2">
                        <label>Price</label>
                        <input id="price" type="number" min="0" step="0.01" class="form-control"
                            required />
                    </div>

                    <div class="col-md-4 p-2">
                        <button type="button" onclick="updateProduct()"
                            class="btn mt-3 w-100 bg-gradient-primary">Update Product</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

@push('script')
    <script>
        const productId = "{{ $product->id }}"; // Pass from controller if needed

        async function loadProduct() {
            try {
                const res = await axios.get(`/backend/admin/products/show/${productId}`);
                const product = res.data.data;

                // Populate form
                document.getElementById('name').value = product.name;
                document.getElementById('description').value = product.description;
                document.getElementById('quantity').value = product.quantity;
                document.getElementById('price').value = product.price;
                document.getElementById('productImage').src = product.image ? '/storage/' + product.image :
                    '/assets/images/no-image.png';

                // Load categories
                // const catRes = await axios.get('/backend/admin/categories'); // API returning
                const catRes = await axios.get('/backend/admin/categories/json');
                // all categories
                const select = document.getElementById('category_id');
                select.innerHTML = '';
                catRes.data.data.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.text = cat.name;
                    if (cat.id === product.category_id) option.selected = true;
                    select.appendChild(option);
                });

            } catch (err) {
                console.error(err);
                alert('Failed to load product data.');
            }
        }

        async function updateProduct() {
            const formData = new FormData();
            formData.append('name', document.getElementById('name').value);
            formData.append('description', document.getElementById('description').value);
            formData.append('quantity', document.getElementById('quantity').value);
            formData.append('price', document.getElementById('price').value);
            formData.append('category_id', document.getElementById('category_id').value);
            formData.append('_method', 'PUT');

            const imageFile = document.getElementById('imageFile').files[0];
            if (imageFile) formData.append('image', imageFile);

            try {
                const res = await axios.post(`/backend/admin/products/update/${productId}`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                if (res.data.status === 'success') {
                    alert(res.data.message || 'Product updated successfully!');
                    window.location.href = '/backend/admin/products/list';
                } else {
                    alert(res.data.message || 'Update failed');
                }

            } catch (err) {
                console.error(err);
                alert('Unexpected error occurred.');
            }
        }

        // Load product on page load
        loadProduct();
    </script>
@endpush
