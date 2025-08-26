<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6 center-screen">
            <div class="card animated fadeIn w-90  p-4">
                <div class="card-body">
                    <h4>EMAIL ADDRESS</h4>
                    <br/>
                    <label>Your email address</label>
                    <input id="email" placeholder="User Email" class="form-control" type="email"/>
                    <br/>
                    <button onclick="verifyEmail()" class="btn w-100 float-end bg-gradient-primary">Next</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
    <script>
        async function verifyEmail() {
            let email = document.getElementById('email').value;

            if (email.length === 0) {
                errorToast('Please enter your email address');
            } else {
                showLoader();
                try {
                    let res = await axios.post('/backend/password/reset/send/otp', { email: email });
                    hideLoader();

                    if (res.status === 200 && res.data.status === true) {
                        successToast(res.data.message);
                        sessionStorage.setItem('email', email);
                        setTimeout(function () {
                            window.location.href = '/verify-otp';
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
