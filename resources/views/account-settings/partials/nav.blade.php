@php
    $isProfile = ($accountSettingsActive ?? '') === 'profile';
    $isPassword = ($accountSettingsActive ?? '') === 'password';
@endphp

<ul class="account-settings-tabs">
    <li>
        <a href="{{ route('account.profile') }}"
           data-account-tab="profile"
           class="{{ $isProfile ? 'active' : '' }}">Profile</a>
    </li>
    <li>
        <a href="{{ route('account.password') }}"
           data-account-tab="password"
           class="{{ $isPassword ? 'active' : '' }}">Change Password</a>
    </li>
</ul>
