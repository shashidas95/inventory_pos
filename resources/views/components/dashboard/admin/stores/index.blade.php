@extends('layouts.sidenav-layout')

@section('content')
    <div class="container mt-4">
        <h3>Manage Store Users</h3>

        @foreach ($stores as $store)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ $store->name }}</h5>
                    <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#store-{{ $store->id }}" role="button"
                        aria-expanded="false" aria-controls="store-{{ $store->id }}">
                        Show/Hide Users
                    </a>
                </div>

                <div class="collapse" id="store-{{ $store->id }}">
                    <div class="card-body">
                        {{-- Existing Users --}}
                        <h6>Current Users</h6>
                        <ul class="list-group mb-3" id="user-list-{{ $store->id }}">
                            @foreach ($store->users as $user)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        {{ $user->name }} - <strong>{{ ucfirst($user->pivot->role) }}</strong>
                                    </div>
                                    <button class="btn btn-sm btn-danger remove-user" data-store="{{ $store->id }}"
                                        data-user="{{ $user->id }}">
                                        Remove
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Assign New User --}}
                        <h6>Assign New User</h6>
                        <div class="row g-2">
                            <div class="col-md-5">
                                <select name="user_id" class="form-select user-select" data-store="{{ $store->id }}">
                                    <option value="">Select User</option>
                                    @foreach ($users as $user)
                                        @if (!$store->users->contains($user))
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="role" class="form-select role-select" data-store="{{ $store->id }}">
                                    <option value="">Select Role</option>
                                    <option value="manager">Manager</option>
                                    <option value="staff">Staff</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-success w-100 assign-user" data-store="{{ $store->id }}">
                                    Assign
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Assign user
            document.querySelectorAll('.assign-user').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const storeId = this.dataset.store;
                    const userSelect = document.querySelector(
                        `.user-select[data-store="${storeId}"]`);
                    const roleSelect = document.querySelector(
                        `.role-select[data-store="${storeId}"]`);
                    const userId = userSelect.value;
                    const role = roleSelect.value;

                    if (!userId || !role) return alert('Please select user & role.');

                    fetch("{{ route('store.users.assign') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                store_id: storeId,
                                user_id: userId,
                                role: role
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            // Add new user to the list
                            const ul = document.getElementById(`user-list-${storeId}`);
                            const li = document.createElement('li');
                            li.className =
                                'list-group-item d-flex justify-content-between align-items-center';
                            li.innerHTML = `
                    <div>${data.user.name} - <strong>${data.role}</strong></div>
                    <button class="btn btn-sm btn-danger remove-user" data-store="${storeId}" data-user="${data.user.id}">Remove</button>
                `;
                            ul.appendChild(li);

                            // Update dropdowns for all stores
                            for (let sId in data.availableUsers) {
                                const select = document.querySelector(
                                    `.user-select[data-store="${sId}"]`);
                                if (!select) continue;
                                select.innerHTML = '<option value="">Select User</option>';
                                data.availableUsers[sId].forEach(u => {
                                    const opt = document.createElement('option');
                                    opt.value = u.id;
                                    opt.text = u.name;
                                    select.appendChild(opt);
                                });
                            }

                            userSelect.value = '';
                            roleSelect.value = '';
                        })
                        .catch(err => console.error(err));
                });
            });

            // Remove user
            document.addEventListener('click', function(e) {
                if (!e.target.classList.contains('remove-user')) return;

                const storeId = e.target.dataset.store;
                const userId = e.target.dataset.user;

                fetch(`/backend/admin/store-users/${storeId}/${userId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        e.target.closest('li').remove();
                        // Update dropdowns for all stores
                        for (let sId in data.availableUsers) {
                            const select = document.querySelector(`.user-select[data-store="${sId}"]`);
                            if (!select) continue;
                            select.innerHTML = '<option value="">Select User</option>';
                            data.availableUsers[sId].forEach(u => {
                                const opt = document.createElement('option');
                                opt.value = u.id;
                                opt.text = u.name;
                                select.appendChild(opt);
                            });
                        }
                    })
                    .catch(err => console.error(err));
            });

        });
    </script>
@endpush
