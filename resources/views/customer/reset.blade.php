@extends('layouts.customer-portal')

@section('title', 'Set Password')

{{-- No portal nav on this public page --}}
@section('portal-nav')
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4 mx-auto mt-5" style="max-width:440px;">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <span class="portal-brand">OIL<span>POS</span></span>
            <p class="text-muted mb-0 mt-2">Set your password</p>
        </div>
        <form method="POST" action="{{ route('customer.reset.submit') }}">
            @csrf
            <input type="hidden" name="shop" value="{{ $shop }}">
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ $email }}" required>
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
            <div class="text-center mt-3"><a href="{{ route('login') }}" class="small">Back to sign in</a></div>
        </form>
    </div>
</div>
@endsection
