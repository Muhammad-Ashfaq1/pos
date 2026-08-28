@extends('auth.layout')

@section('title')
    Verify Email - {{ config('app.name') }}
@endsection

@section('content')
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-6">
            <div class="card">
                <div class="card-body">
                    <div class="app-brand justify-content-center mb-6">
                        <a href="{{ url('/') }}" class="app-brand-link">
                            <span class="app-brand-logo demo">
                                @include('layouts.partials.brand-logo')
                            </span>
                            <span class="app-brand-text demo text-heading fw-bold ms-2">{{ config('app.name') }}</span>
                        </a>
                    </div>

                    <h4 class="mb-1">Verify your email</h4>
                    <p class="mb-4">
                        Thanks for signing up. Before continuing, please check your inbox for a verification link.
                        If you did not receive the email, you can request another below.
                    </p>

                    @if (session('status'))
                        <div class="alert alert-success mb-4" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form action="{{ route('verification.send') }}" method="POST" class="mb-4">
                        @csrf
                        <button type="submit" class="btn btn-primary d-grid w-100">
                            Resend verification email
                        </button>
                    </form>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-label-secondary d-grid w-100">
                            Log out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
