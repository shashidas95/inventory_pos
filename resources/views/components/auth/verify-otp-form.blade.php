<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6 center-screen">
            <div class="card animated fadeIn w-90  p-4">
                <div class="card-body">
                    <h4>ENTER OTP CODE</h4>
                    <br />
                    <label>6 Digit Code Here</label>
                    <input id="otp" placeholder="Code" class="form-control" type="text" />
                    <br />
                    <button onclick="verifyOtp()" class="btn w-100 float-end bg-gradient-primary">Next</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
    <script>
        async function verifyOtp() {

            let otp = document.getElementById('otp').value;

            if (otp.length !== 6) {
                errorToast('OTP must be 6 digits long');
            } else {
                showLoader();
                try {
                    let res = await axios.post('/backend/password/reset/verify/otp', {
                        otp: otp,
                        email: sessionStorage.getItem('email')
                    });

                    hideLoader();

                    if (res.status === 200 && res.data.status === true) {
                        successToast(res.data.message);
                        sessionStorage.clear();
                        setTimeout(() => {
                            window.location.href = '/reset-password';
                        }, 2000);
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
                    } else {
                        errorToast("Something went wrong");
                    }
                }
            }
        }
    </script>
@endpush
