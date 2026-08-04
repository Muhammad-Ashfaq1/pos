@extends('auth.layout')

@section('title')
    Reset Password - {{ config('app.name') }}
@endsection

@section('content')
<div class="authentication-wrapper authentication-basic container-p-y">
  <div class="authentication-inner py-6">
    <div class="card">
      <div class="card-body">
        <div class="app-brand justify-content-center mb-6">
          <a href="{{ route('login') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
              @include('layouts.partials.brand-logo')
            </span>
            <span class="app-brand-text demo text-heading fw-bold ms-2">{{ config('app.name') }}</span>
          </a>
        </div>

        <h4 class="mb-1">Reset Password</h4>
        <p class="mb-6">Your new password must be different from previously used passwords.</p>

        <form method="POST" action="{{ route('password.update') }}" id="formResetPassword">
          @csrf
          <input type="hidden" name="token" value="{{ $token }}">

          <div class="mb-6 form-control-validation">
            <label class="form-label" for="email">Email</label>
            <input
              type="email"
              id="email"
              name="email"
              class="form-control"
              value="{{ old('email', $email) }}"
              placeholder="Enter your email"
              @if (filled($email) && ! $errors->has('email')) readonly @endif
              required
              autocomplete="username" />
          </div>

          <div class="mb-6 form-password-toggle form-control-validation">
            <label class="form-label" for="password">New Password</label>
            <div class="input-group input-group-merge">
              <input
                type="password"
                id="password"
                class="form-control"
                name="password"
                placeholder="••••••••••••"
                aria-describedby="password"
                required
                autofocus
                autocomplete="new-password" />
              <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
            </div>
          </div>

          <div class="mb-6 form-password-toggle form-control-validation">
            <label class="form-label" for="password_confirmation">Confirm Password</label>
            <div class="input-group input-group-merge">
              <input
                type="password"
                id="password_confirmation"
                class="form-control"
                name="password_confirmation"
                placeholder="••••••••••••"
                aria-describedby="password_confirmation"
                required
                autocomplete="new-password" />
              <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
            </div>
          </div>

          <button type="submit" class="btn btn-primary d-grid w-100 mb-6">
            Set new password
          </button>

          <div class="text-center">
            <a href="{{ route('login') }}">Back to login</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
