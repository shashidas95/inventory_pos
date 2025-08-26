<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6 center-screen">
            <div class="card animated fadeIn w-90 p-4">
                <div class="card-body">
                    <h4>SET NEW PASSWORD</h4>
                    <br/>
                    <label>New Password</label>
                    <input id="password" placeholder="New Password" class="form-control" type="password"/>
                    <br/>
                    <label>Confirm Password</label>
                    <input id="passwordConfirmation" placeholder="Confirm Password" class="form-control" type="password"/>
                    <br/>
                    <button onclick="resetPassword()" class="btn w-100 bg-gradient-primary">Next</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
    <script>
        async function resetPassword() {
            let password = document.getElementById('password').value;
            let passwordConfirmation = document.getElementById('passwordConfirmation').value;

            if (password.length === 0) {
                errorToast('Password is required');
            } else if (passwordConfirmation.length === 0) {
                errorToast('Confirm Password is required');
            } else if (password !== passwordConfirmation) {
                errorToast('Password and Confirm Password must be same');
            } else {
                showLoader();
                try {
                    let res = await axios.post('/backend/password/reset', {
                        password: password,
                        password_confirmation: passwordConfirmation
                    });

                    hideLoader();

                    if (res.status === 200 && res.data.status === true) {
                        successToast(res.data.message);
                        setTimeout(() => {
                            window.location.href = '/login';
                        }, 1000);
                    } else if (res.response.status === 422) {
                        let errors = res.response.data.errors;
                        for (let field in errors) {
                            if (errors.hasOwnProperty(field)) {
                                errorToast(errors[field][0]);
                            }
                        }
                    } else {
                        errorToast(res.data.message);
                    }

                } catch (err) {
                    hideLoader();
                    if (err.response) {
                        let errors = err.response.data.errors;
                        if (Array.isArray(errors)) {
                            errors.forEach(msg => errorToast(msg));
                        } else {
                            for (let field in errors) {
                                if (errors.hasOwnProperty(field)) {
                                    errorToast(errors[field][0]);
                                }
                            }
                        }
                    }else{
                        errorToast("Something went wrong");
                    }
                }
            }
        }
    </script>
@endpush
