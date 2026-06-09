@extends('layouts.customer-portal')

@section('title', 'Customer Sign In')

@section('content')
<div class="container">
    <div class="auth-card card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <span class="portal-brand">OIL<span>POS</span></span>
                <p class="text-muted mb-0 mt-2">Customer Portal</p>
            </div>

            <ul class="nav nav-pills nav-justified mb-4" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-login" type="button">Sign In</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-register" type="button">Register</button></li>
            </ul>

            <div class="tab-content">
                {{-- LOGIN --}}
                <div class="tab-pane fade show active" id="tab-login">
                    <form id="login-form">
                        <div class="mb-3">
                            <label class="form-label">Shop code</label>
                            <input type="text" name="shop" class="form-control" placeholder="e.g. rapid-lube-downtown" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="text-end mb-3">
                            <a href="#" id="forgot-link" class="small text-primary">Forgot password?</a>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Sign In</button>
                    </form>
                </div>

                {{-- REGISTER --}}
                <div class="tab-pane fade" id="tab-register">
                    <form id="register-form">
                        <div class="mb-3">
                            <label class="form-label">Shop code</label>
                            <input type="text" name="shop" class="form-control" placeholder="e.g. rapid-lube-downtown" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone <span class="text-muted small">(optional)</span></label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Create account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Forgot password modal --}}
<div class="modal fade" id="forgotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Reset password</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="forgot-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Shop code</label>
                        <input type="text" name="shop" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send reset link</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
