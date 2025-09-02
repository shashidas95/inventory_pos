@extends('layouts.sidenav-layout')

@section('content')
<div class="container">
    <div class="row">

        <!-- Create Customer Form -->
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h5>Create Customer</h5>
                <hr>
                <div id="alertMessage"></div>
                <form id="customerForm">
                    <div class="mb-2">
                        <label>Name</label>
                        <input type="text" id="name" class="form-control" required>
                        <small class="text-danger" id="nameError"></small>
                    </div>
                    <div class="mb-2">
                        <label>Email</label>
                        <input type="email" id="email" class="form-control" required>
                        <small class="text-danger" id="emailError"></small>
                    </div>
                    <div class="mb-2">
                        <label>Password</label>
                        <input type="password" id="password" class="form-control" required>
                        <small class="text-danger" id="passwordError"></small>
                    </div>
                    <button type="button" class="btn btn-primary w-100" onclick="createCustomer()">Create Customer</button>
                </form>
            </div>
        </div>

        <!-- Customers List -->
        <div class="col-md-8">
            <div class="card p-3 shadow-sm">
                <h5>Customers List</h5>
                <hr>
                <table class="table table-bordered" id="customersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody id="customersList"></tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@push('script')
<script>
    // Load customers dynamically
    async function loadCustomers() {
        try {
            const res = await axios.get("{{ route('admin.customers.list') }}");
            const tbody = document.getElementById('customersList');
            tbody.innerHTML = '';

            if (res.data.status === 'success') {
                res.data.data.forEach(c => {
                    tbody.innerHTML += `<tr>
                        <td>${c.id}</td>
                        <td>${c.name}</td>
                        <td>${c.email}</td>
                    </tr>`;
                });
            }
        } catch (err) {
            console.error(err);
            alert('Failed to load customers.');
        }
    }

    // Create customer via AJAX
    async function createCustomer() {
        // Clear previous errors
        document.getElementById('nameError').innerText = '';
        document.getElementById('emailError').innerText = '';
        document.getElementById('passwordError').innerText = '';

        const formData = new FormData();
        formData.append('name', document.getElementById('name').value);
        formData.append('email', document.getElementById('email').value);
        formData.append('password', document.getElementById('password').value);

        try {
            const res = await axios.post("{{ route('admin.customers.store') }}", formData);

            if (res.data.status === 'success') {
                Toastify({
                    text: res.data.message,
                    backgroundColor: "green",
                    duration: 3000
                }).showToast();

                document.getElementById('customerForm').reset();
                loadCustomers();
            }
        } catch (err) {
            if (err.response && err.response.status === 422) {
                const errors = err.response.data.errors;
                if (errors.name) document.getElementById('nameError').innerText = errors.name[0];
                if (errors.email) document.getElementById('emailError').innerText = errors.email[0];
                if (errors.password) document.getElementById('passwordError').innerText = errors.password[0];
            } else {
                console.error(err);
                alert('Unexpected error occurred.');
            }
        }
    }

    // Load customers on page load
    document.addEventListener('DOMContentLoaded', loadCustomers);
</script>
@endpush
