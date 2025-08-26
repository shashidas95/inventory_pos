<div class="card">
    <div class="card-body">
        <h4>Products List</h4>
        <hr />
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>
                            <img src="{{ asset('storage/' . $product->image) }}"
                                style="width:60px; height:60px; object-fit:cover;" />
                        </td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category ? $product->category->name : '' }}</td>
                        <td>{{ $product->description }}</td>
                        <td>{{ $product->price }}</td>
                        <td>{{ $product->quantity }}</td>
                        <td>
                            <a href="{{ route('admin.products.adminProductEdit', $product->id) }}">Edit</a>
                        </td>
                        <td>
                            <a href="{{ route('admin.products.adminProductEdit', $product->id) }}"
                                class="btn btn-sm btn-warning">Edit</a>

                            <form action="{{ route('admin.products.adminProductDelete', $product->id) }}" method="POST"
                                style="display:inline-block;"
                                onsubmit="return confirm('Are you sure you want to delete this product?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
