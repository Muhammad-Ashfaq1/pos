@php
    $currentLocale = app()->getLocale();
@endphp

<div class="dropdown">
    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        {{ $currentLocale === 'ar' ? __('app.language_arabic') : __('app.language_english') }}
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item {{ $currentLocale === 'en' ? 'active' : '' }}" href="{{ route('language.switch', 'en') }}">
                {{ __('app.language_english') }}
            </a>
        </li>
        <li>
            <a class="dropdown-item {{ $currentLocale === 'ar' ? 'active' : '' }}" href="{{ route('language.switch', 'ar') }}">
                {{ __('app.language_arabic') }}
            </a>
        </li>
    </ul>
</div>
