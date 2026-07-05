@extends('auth.layout')
@section('title')
    Forgot Password - {{ config('app.name') }}
@endsection

@section('content')
<div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6">
      <!-- Register Card -->
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
          <h4 class="mb-1">Forgot your password? 🔒</h4>
          <p class="mb-6">Enter your email and we'll send you a link to reset your password.</p>


       <form id="formAuthentication" class="mb-6" action="{{ route('password.email') }}" method="POST">
    @csrf



            <div class="mb-6 form-control-validation">
              <label for="email" class="form-label">Email</label>
              <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email" />
            </div>
              <button class="btn btn-primary d-grid w-100">Send Reset Link</button>
          </form>

          <p class="text-center">
            <span>Already have an account?</span>
            <a href="{{ route('login') }}">
              <span>Sign in instead</span>
            </a>
          </p>
        </div>
      </div>
      <!-- Register Card -->
    </div>
  </div>
@endsection
