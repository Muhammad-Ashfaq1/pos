@extends('emails.layout')

@section('content')
    <h1 class="email-title">Shop account {{ $status }}</h1>

    <p class="email-text">
        Hello{{ ! empty($name) ? ' '.$name : '' }},
    </p>

    <p class="email-text">
        Your shop account for <strong>{{ $shopName }}</strong> is now <strong>{{ $status }}</strong>.
    </p>

    @if (! empty($reason))
        <p class="email-text">
            <strong>Reason:</strong> {{ $reason }}
        </p>
    @endif

    <p class="email-text" style="margin-bottom: 0;">
        If you believe this is a mistake, please contact support.
    </p>
@endsection
