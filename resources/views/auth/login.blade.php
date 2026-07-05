@extends('auth.layout')

@section('title')
    Login - {{ config('app.name') }}
@endsection

@section('content')
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner py-6">
        <!-- Login -->
        <div class="card">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-6">
              <a href="{{ url('/') }}" class="app-brand-link">
                <span class="app-brand-logo demo">
                  @include('layouts.partials.brand-logo')
                </span>
                <span class="app-brand-text demo text-heading fw-bold ms-2">{{ config('app.name') }}</span>
              </a>
            </div>
            <!-- /Logo -->
            <h4 class="mb-1">Welcome back</h4>
            <p class="mb-6">Sign in to continue into your platform or tenant workspace.</p>

            <form action="{{ route('login.submit') }}" method="POST">
    @csrf

              <div class="mb-6 form-control-validation">
                <label for="email" class="form-label">Email or Username</label>
                <input
  type="email"
  class="form-control"
  id="email"
  name="email"
  value="{{ old('email') }}"
  placeholder="Enter your email"
  required
  autofocus />

              </div>
              <div class="mb-6 form-password-toggle form-control-validation">
                <label class="form-label" for="password">Password</label>
                <div class="input-group input-group-merge">
                  <input
                    type="password"
                    id="password"
                    class="form-control"
                    name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" />
                  <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                </div>
              </div>
              <div class="my-8">
                <div class="d-flex justify-content-between">
                  <div class="form-check mb-0 ms-2">
                    <label class="form-check-label" for="remember-me"> Remember Me </label>
                    <input
  class="form-check-input"
  type="checkbox"
  id="remember-me"
  name="remember" />

                  </div>
                  <a href="{{ route('forgot') }}">
                    <p class="mb-0">Forgot Password?</p>
                  </a>
                </div>
              </div>
              <div class="mb-6">
                <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
              </div>
            </form>

            <p class="text-center">
              <span>New on our platform?</span>
              <a href="{{ route('register') }}">
                <span>Create an account</span>
              </a>
            </p>
          </div>
        </div>
        <!-- /Login -->
      </div>
    </div>
@endsection

