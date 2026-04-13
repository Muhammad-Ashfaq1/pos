@extends('auth.layout')

@section('title')
    Reset - {{ config('app.name') }}
@endsection

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6">

      <div class="card">
        <div class="card-body">

          <!-- Logo -->
          <div class="app-brand justify-content-center mb-6">
            <a href="{{ route('login') }}" class="app-brand-link">
              <span class="app-brand-text demo text-heading fw-bold">Vuexy</span>
            </a>
          </div>

          <h4 class="mb-1">Reset Password 🔒</h4>
          <p class="mb-6">
            <span class="fw-medium">
              Your new password must be different from previously used passwords
            </span>
          </p>

          <!-- ✅ CORRECT FORM -->
          <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <!-- REQUIRED -->
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ request()->email }}">

            <div class="mb-6">
              <label class="form-label">New Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-6">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary d-grid w-100 mb-6">
              Set new password
            </button>

            <div class="text-center">
              <a href="{{ route('login') }}" class="d-flex justify-content-center">
                Back to login
              </a>
            </div>
          </form>

        </div>
      </div>

    </div>
  </div>
</div>
@endsection
