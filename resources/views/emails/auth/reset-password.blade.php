@extends('emails.layout')

@section('content')
    <h1 class="email-title">Reset your password</h1>

    <p class="email-text">
        Hello{{ ! empty($name) ? ' '.$name : '' }},
    </p>

    <p class="email-text">
        We received a request to reset the password for your {{ config('app.name') }} account.
        Click the button below to choose a new password.
    </p>

    <div class="btn-wrap">
        <a href="{{ $url }}" class="btn-primary" target="_blank" rel="noopener">
            Reset Password
        </a>
    </div>

    <p class="email-text">
        This link expires in <strong>{{ $expireMinutes }} minutes</strong>.
        If you did not request a password reset, no further action is required.
    </p>

    <p class="email-text" style="margin-bottom: 0; font-size: 13px; word-break: break-all;">
        If the button does not work, copy and paste this URL into your browser:<br />
        <a href="{{ $url }}">{{ $url }}</a>
    </p>
@endsection
