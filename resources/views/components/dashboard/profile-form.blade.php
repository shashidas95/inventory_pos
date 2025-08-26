<div class="container">
    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="card animated fadeIn w-100 p-3">
                <div class="card-body">
                    <h4>User Profile</h4>
                    <hr />
                    <div class="container-fluid m-0 p-0">
                        <div class="row m-0 p-0">

                            <div class="col-md-8">

                                <div class="row">

                                    <div class="col-md-4 p-2">
                                        <label>Role</label>
                                        <input id="role" readonly class="form-control" type="text" />
                                    </div>
                                    <div class="col-md-4 p-2">
                                        <label>Email Address</label>
                                        <input id="email" placeholder="User Email" class="form-control" readonly
                                            type="email" />
                                    </div>
                                    <div class="col-md-4 p-2">
                                        <label>Full Name</label>
                                        <input id="name" placeholder="Full Name" class="form-control"
                                            type="text" />
                                    </div>
                                    <div class="col-md-4 p-2">
                                        <label>Phone Number</label>
                                        <input id="phone" placeholder="Phone Number" class="form-control"
                                            type="text" />
                                    </div>
                                    <div class="col-md-4 p-2">
                                        <label>Address</label>
                                        <textarea id="address" placeholder="Address" class="form-control"></textarea>
                                    </div>

                                </div>

                            </div>
                            <div class="col-md-4">

                                <div class="row">

                                    <div class="col-md-8 p-2">
                                        <label>Profile Image</label>
                                        <input id="profileImageFile" readonly class="form-control" type="file" />
                                    </div>
                                    <div class="col-md-4 p-2">

                                        <img id="profileImage" src="" alt="profile Image" width="150">

                                    </div>

                                </div>

                            </div>




                        </div>
                        <div class="row m-0 p-0">
                            <div class="col-md-4 p-2">
                                <button onclick="onUpdate()" class="btn mt-3 w-100  bg-gradient-primary">Update</button>
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
        async function getProfile() {
            showLoader();

            // get profile data:
            let res = await axios.get("/backend/profile")

            hideLoader();

            if (res.status === 200) {
                // console.log(res.data.data);
                let data = res.data.data;
                document.getElementById('role').value = data.role;
                document.getElementById('email').value = data.email;
                document.getElementById('name').value = data.name;
                document.getElementById('phone').value = data.phone;
                document.getElementById('address').value = data.address;
                document.getElementById('profileImage').src = data.image;
            } else {
                errorToast(res.data['message'])
            }
        }

        async function onUpdate() {
            let name = document.getElementById('name').value;
            let phone = document.getElementById('phone').value;
            let address = document.getElementById('address').value;

            let profileInput = document.getElementById('profileImageFile');
            let avatarImage = profileInput.files[0];

            // ✅ Add return after validation to prevent continuing on invalid input
            if (name.length === 0) {
                errorToast('First Name is required');
                return;
            } else if (phone.length === 0) {
                errorToast('Phone is required');
                return;
            } else if (address.length === 0) {
                errorToast('Address is required');
                return;
            }

            showLoader();

            try {
                let formData = new FormData();
                formData.append('name', name);
                formData.append('phone', phone);
                formData.append('address', address);

                if (avatarImage) {
                    formData.append('image', avatarImage);
                }

                let res = await axios.post("/backend/profile-update", formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
               
                hideLoader();

                if (res.status === 200 && res.data.status === true) {
                    localStorage.setItem('user', JSON.stringify(res.data.data));

                    // ✅ Safely update DOM with new image and name
                    document.getElementById('userAvatar').src = res.data.data.image;
                    document.getElementById('userDropdownAvatar').src = res.data.data.image;
                    document.getElementById('profileImage').src = res.data.data.image;
                    document.getElementById('loginUserName').innerText = res.data.data.name;

                    successToast(res.data.message);
                    setTimeout(() => {
                        window.location.href = '/profile';
                    }, 3000);
                } else {
                    errorToast(res.data.message || 'Something went wrong.');
                }

            } catch (err) {
                hideLoader();

                //  422 Validation errors go here
                if (err.response && err.response.status === 422) {
                    let errors = err.response.data.errors;
                    for (let field in errors) {
                        if (errors.hasOwnProperty(field)) {
                            errorToast(errors[field][0]);
                        }
                    }
                }

                //  Auth error
                else if (err.response && err.response.status === 401) {
                    errorToast(err.response.data.message || 'Unauthorized.');
                }

                //  Fallback error handler
                else {
                    console.error(err); // For debugging
                    errorToast('Unexpected error occurred.');
                }
            }
        }
        getProfile()
    </script>
@endpush
