@extends('layouts.customer-portal')

@section('title', 'Set Password')

@section('content')
<div class="container">
    <div class="auth-card card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <span class="portal-brand">OIL<span>POS</span></span>
                <p class="text-muted mb-0 mt-2">Set your password</p>
            </div>
            <form id="reset-form">
                <input type="hidden" name="shop" value="{{ request('shop') }}">
                <input type="hidden" name="token" value="{{ request('token') }}">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ request('email') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold">Save password</button>
            </form>
        </div>
    </div>
</div>
@endsection
