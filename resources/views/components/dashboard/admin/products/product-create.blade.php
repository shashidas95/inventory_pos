<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-10 center-screen">
            <div class="card animated fadeIn w-100 p-3">
                <div class="card-body">
                    <h4>Create Product</h4>
                    <hr />
                    <div class="container-fluid m-0 p-0">
                        <div class="row m-0 p-0">
                            <div class="col-md-4 p-2">
                                <label>Product Name</label>
                                <input id="name" placeholder="Enter Product Name" class="form-control"
                                    type="text" />
                            </div>
                            <div class="col-md-4 p-2">
                                <label>Category</label>
                                <select id="category_id" class="form-control">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 p-2">
                                <label>Price</label>
                                <input id="price" placeholder="Enter Price" class="form-control" type="number"
                                    step="0.01" />
                            </div>
                            <div class="col-md-4 p-2">
                                <label>Quantity</label>
                                <input id="quantity" placeholder="Enter Quantity" class="form-control"
                                    type="number" />
                            </div>
                            <div class="col-md-4 p-2">
                                <label>Description</label>
                                <textarea id="description" placeholder="Enter Product Description" class="form-control"></textarea>
                            </div>
                            <div class="col-md-4 p-2">
                                <label>Product Image</label>
                                <input id="image" class="form-control" type="file" />
                            </div>
                        </div>
                        <div class="row m-0 p-0">
                            <div class="col-md-4 p-2">
                                <button onclick="onProductCreate()"
                                    class="btn mt-3 w-100 bg-gradient-primary">Create</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
    <script>
        async function onProductCreate() {
            let name = document.getElementById('name').value;
            let category_id = document.getElementById('category_id').value;
            let price = document.getElementById('price').value;
            let quantity = document.getElementById('quantity').value;
            let description = document.getElementById('description').value;
            let image = document.getElementById('image').files[0];

            // Validation
            if (!name) return errorToast('Product name is required');
            if (!category_id) return errorToast('Category is required');
            if (!price) return errorToast('Price is required');
            if (!quantity) return errorToast('Quantity is required');
            if (!description) return errorToast('Description is required');
            if (!image) return errorToast('Image is required');

            let formData = new FormData();
            formData.append('name', name);
            formData.append('category_id', category_id);
            formData.append('price', price);
            formData.append('quantity', quantity);
            formData.append('description', description);
            formData.append('image', image);

            showLoader();
            try {
                let res = await axios.post("/backend/admin/products/store", formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                hideLoader();

                if (res.status === 201 && res.data.status === true) {
                    successToast(res.data.message);
                    setTimeout(() => {
                        window.location.href = '/product-list';
                    }, 2000);
                } else {
                    errorToast(res.data.message || 'Something went wrong');
                }

            } catch (err) {
                hideLoader();
                if (err.response && err.response.status === 422) {
                    let errors = err.response.data.errors;
                    for (let field in errors) {
                        if (errors.hasOwnProperty(field)) {
                            errorToast(errors[field][0]);
                        }
                    }
                } else {
                    errorToast("Unexpected error occurred.");
                    console.error(err);
                }
            }
        }
    </script>
@endpush
