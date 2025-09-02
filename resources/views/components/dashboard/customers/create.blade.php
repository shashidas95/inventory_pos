<div class="container">
    <div class="card p-4 shadow-sm rounded">
        <h4 class="mb-3">Create New Customer</h4>
        <hr />
        <div id="alertMessage"></div>

        <form id="customerForm">
            <div class="row">

                <!-- Name -->
                <div class="col-md-6 mb-3">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" class="form-control" placeholder="Enter full name" required>
                    <small class="text-danger" id="nameError"></small>
                </div>

                <!-- Email -->
                <div class="col-md-6 mb-3">
                    <label for="email">Email</label>
                    <input type="email" id="email" class="form-control" placeholder="Enter email" required>
                    <small class="text-danger" id="emailError"></small>
                </div>

                <!-- Password -->
                <div class="col-md-6 mb-3">
                    <label for="password">Password</label>
                    <input type="password" id="password" class="form-control" placeholder="Enter password" required>
                    <small class="text-danger" id="passwordError"></small>
                </div>

                <!-- Confirm Password -->
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" class="form-control"
                        placeholder="Confirm password" required>
                    <small class="text-danger" id="passwordConfirmError"></small>
                </div>

                <!-- Submit -->
                <div class="col-md-4 mb-3">
                    <button type="button" onclick="createCustomer()" class="btn btn-primary w-100 mt-2">
                        Create Customer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('script')
    <script>
        async function createCustomer() {
            // Clear previous errors
            ['nameError', 'emailError', 'passwordError', 'passwordConfirmError', 'alertMessage'].forEach(id => {
                document.getElementById(id).innerText = '';
            });

            const formData = {
                name: document.getElementById('name').value.trim(),
                email: document.getElementById('email').value.trim(),
                password: document.getElementById('password').value,
                password_confirmation: document.getElementById('password_confirmation').value,
            };

            try {
                const res = await axios.post('{{ route('admin.customers.store') }}', formData);

                if (res.status === 200) {
                    // Success alert
                    const alertDiv = document.getElementById('alertMessage');
                    alertDiv.innerHTML =
                        `<div class="alert alert-success">${res.data.message || 'Customer created successfully!'}</div>`;

                    // Reset form
                    document.getElementById('customerForm').reset();
                }
            } catch (err) {
                if (err.response && err.response.status === 422) {
                    const errors = err.response.data.errors;

                    if (errors.name) document.getElementById('nameError').innerText = errors.name[0];
                    if (errors.email) document.getElementById('emailError').innerText = errors.email[0];
                    if (errors.password) document.getElementById('passwordError').innerText = errors.password[0];
                    if (errors.password_confirmation) document.getElementById('passwordConfirmError').innerText = errors
                        .password_confirmation[0];
                } else {
                    document.getElementById('alertMessage').innerHTML =
                        `<div class="alert alert-danger">Unexpected error occurred.</div>`;
                    console.error(err);
                }
            }
        }
    </script>
@endpush
