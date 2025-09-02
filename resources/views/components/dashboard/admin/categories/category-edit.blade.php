<div class="container">
    <h3>Edit Category</h3>
    <form action="{{ route('categories.update', $category) }}" method="POST">
        @csrf
        {{-- @method('PUT') --}}
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" value="{{ $category->name }}" class="form-control" required>
        </div>
        <button class="btn btn-success">Update</button>
    </form>
</div>
