@extends('auth.layout')

@section('title')
    Register - {{ config('app.name') }}
@endsection

@section('content')
<div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6" style="max-width: 900px;">
        <div class="card shadow-sm">
            <div class="card-body">

                
                <div class="text-center mb-4">
                    <h3 class="fw-bold">Shops</h3>
                    <h5 class="mb-1">Adventure starts here 🚀</h5>
                    <p class="text-muted">Create your shop and start managing everything easily.</p>
                </div>

                <form id="shop-registration-form" action="{{ route('register.store') }}" method="POST" novalidate>
                    @csrf

                    <!-- OWNER DETAILS -->
                    <h5 class="mb-3">Owner Details</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Owner Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name') }}" placeholder="Enter your full name" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control"
                                value="{{ old('email') }}" placeholder="Enter your email" required>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    
                     <div class="row">
                        <div class="col-md-6 mb-3 form-password-toggle form-control-validation">
                            <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password" placeholder="••••••••••••" required />
                                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                            </div>
                            @error('password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3 form-password-toggle form-control-validation">
                            <label class="form-label" for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="confirm_password" class="form-control" name="password_confirmation" placeholder="••••••••••••" required />
                                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                            </div>
                            @error('password_confirmation')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>


                    <!-- PHONE -->
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control"
                            value="{{ old('phone') }}" placeholder="03xxxxxxxxx">
                        @error('phone')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- SHOP DETAILS -->
                    <h5 class="mb-3 mt-4">Shop Details</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Shop Name <span class="text-danger">*</span></label>
                            <input type="text" name="shop_name" class="form-control"
                                value="{{ old('shop_name') }}" placeholder="Enter your shop name" required>
                            @error('shop_name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Website URL</label>
                            <input type="url" name="website_url" class="form-control"
                                value="{{ old('website_url') }}"
                                placeholder="https://yourshop.com">
                            <small class="text-muted">Example: https://yourshop.com</small>
                            @error('website_url')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <!-- BUSINESS TYPE + COUNTRY -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Business Type</label>
                            <input type="text" name="business_type" class="form-control"
                                value="{{ old('business_type') }}" placeholder="Clothing, Grocery, Pharmacy">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control"
                                value="{{ old('country') }}" placeholder="Country">
                        </div>
                    </div>

                    <!-- CITY + STATE -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control"
                                value="{{ old('city') }}" placeholder="City">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control"
                                value="{{ old('state') }}" placeholder="State">
                        </div>
                    </div>

                    <!-- ADDRESS LAST -->
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control"
                            value="{{ old('address') }}" placeholder="Enter your address">
                    </div>

                    <!-- TERMS -->
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#">Privacy Policy & Terms</a>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">
                        Create Account
                    </button>
                </form>

                <p class="text-center mt-4">
                    Already have an account?
                    <a href="{{ route('login') }}">Sign in instead</a>
                </p>

            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')
<script>
$(function () {
    $('#shop-registration-form').validate({
        errorClass: 'is-invalid',
        validClass: 'is-valid',
        errorElement: 'small',
        errorPlacement: function (error, element) {
            error.addClass('text-danger');
            error.insertAfter(element.closest('.input-group').length ? element.closest('.input-group') : element);
        },
        highlight: function (element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        },
        rules: {
            name: { required: true, maxlength: 255 },
            email: { required: true, email: true, maxlength: 255 },
            password: { required: true, minlength: 8, maxlength: 64 },
            password_confirmation: { required: true, equalTo: '#password' },
            phone: { required: true, maxlength: 30 },
            shop_name: { required: true, maxlength: 255 },
            website_url: { url: true, maxlength: 255 },
            address: { required: true, maxlength: 1000 },
            city: { required: true, maxlength: 255 },
            country: { required: true, maxlength: 255 },
            terms: { required: true }
        }
    });
});
</script>
@endsection
