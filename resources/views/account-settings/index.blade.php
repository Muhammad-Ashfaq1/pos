@extends($accountSettingsLayout)

@section('title', ($accountSettingsActive ?? 'profile') === 'password' ? 'Change Password' : 'Profile')

@php
    $isEmployeeAccountSettings = ($accountSettingsLayout ?? '') === 'layouts.employee-portal';
    $accountSettingsPageTitle = ($accountSettingsActive ?? 'profile') === 'password' ? 'Change Password' : 'Profile';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pos-glass.css') }}?v={{ filemtime(public_path('assets/css/pos-glass.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/account-settings.css') }}?v={{ filemtime(public_path('assets/css/account-settings.css')) }}">
    @if ($isEmployeeAccountSettings)
        <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}">
    @endif
@endpush

@section('content')
    @if ($isEmployeeAccountSettings)
        <div class="employee-orders-page employee-orders-glass">
            <x-employee.page-header
                :title="$accountSettingsPageTitle"
                :back-url="route('employee.dashboard')"
                back-title="Back to dashboard"
            />
    @endif

<<<<<<< Updated upstream
    <div class="account-settings-card{{ $isEmployeeAccountSettings ? ' pos-glass-card pos-tone-primary' : ' pos-glass-card pos-tone-secondary pos-settings-panel' }}" id="account-settings"
         data-profile-url="{{ route('account.profile') }}"
         data-password-url="{{ route('account.password') }}"
         data-active="{{ $accountSettingsActive ?? 'profile' }}">
        @include('account-settings.partials.nav')
=======
    <div class="account-settings-shell">
        <div class="account-settings-card pos-glass-card pos-tone-secondary"
             id="account-settings"
             data-profile-url="{{ route('account.profile') }}"
             data-password-url="{{ route('account.password') }}"
             data-active="{{ $accountSettingsActive ?? 'profile' }}">
            @include('account-settings.partials.nav')
>>>>>>> Stashed changes

            <div class="account-settings-panel"
                 data-panel="profile"
                 @if (($accountSettingsActive ?? 'profile') !== 'profile') hidden @endif>
                @include('account-settings.partials.profile-form')
                @if (auth()->user() instanceof \App\Models\User)
                    @include('partials._theme-picker')
                @endif

                @if (auth()->user()?->isEmployee())
                    @include('account-settings.partials.workspace-form')
                @endif
            </div>

            <div class="account-settings-panel"
                 data-panel="password"
                 @if (($accountSettingsActive ?? '') !== 'password') hidden @endif>
                @include('account-settings.partials.password-form')
            </div>
        </div>
    </div>

    @if ($isEmployeeAccountSettings)
        </div>
    @endif
@endsection

@push('page-script')
<script>
    (function () {
        const root = document.getElementById('account-settings');
        if (!root) return;

        const urls = {
            profile: root.dataset.profileUrl,
            password: root.dataset.passwordUrl,
        };
        const tabs = root.querySelectorAll('[data-account-tab]');
        const panels = root.querySelectorAll('[data-panel]');
        const titles = {
            profile: 'Profile',
            password: 'Change Password',
        };

        function setActive(tab, push) {
            if (!urls[tab]) return;

            const changed = root.dataset.active !== tab;

            tabs.forEach((el) => {
                el.classList.toggle('active', el.dataset.accountTab === tab);
            });

            panels.forEach((panel) => {
                panel.hidden = panel.dataset.panel !== tab;
            });

            root.dataset.active = tab;
            document.title = titles[tab] || document.title;

            const pageTitle = document.querySelector('.employee-orders-page .employee-orders-title');
            if (pageTitle && titles[tab]) {
                pageTitle.textContent = titles[tab];
            }

            if (push && changed) {
                history.pushState({ accountSettingsTab: tab }, titles[tab], urls[tab]);
            }
        }

        tabs.forEach((tab) => {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                setActive(this.dataset.accountTab, true);
            });
        });

        window.addEventListener('popstate', function (e) {
            const tab = (e.state && e.state.accountSettingsTab)
                || (window.location.pathname.indexOf('/account/password') !== -1 ? 'password' : 'profile');
            setActive(tab, false);
        });

        history.replaceState(
            { accountSettingsTab: root.dataset.active || 'profile' },
            document.title,
            window.location.href
        );

        // Avatar preview (single handler)
        const fileInput = document.getElementById('account_avatar');
        const preview = document.getElementById('account-avatar-preview');

        function fieldGroup(input) {
            return input ? input.closest('.input-group') : null;
        }

        function errorEl(form, name) {
            return form.querySelector('[data-error-for="' + name + '"]');
        }

        function setFieldError(form, input, name, message) {
            const group = fieldGroup(input);
            const err = errorEl(form, name);
            if (input) input.classList.toggle('is-invalid', !!message);
            if (group) group.classList.toggle('is-invalid', !!message);
            if (err) {
                err.textContent = message || '';
                err.classList.toggle('is-visible', !!message);
                err.style.display = message ? 'block' : 'none';
            }
        }

        function clearFieldError(form, input, name) {
            setFieldError(form, input, name, '');
        }

        function focusFirstInvalid(form) {
            const firstInvalid = form.querySelector('.form-control.is-invalid, input.is-invalid');
            if (firstInvalid) firstInvalid.focus();
        }

        // Password visibility: Helpers.initPasswordToggle() (main.js) owns .form-password-toggle

        // Profile form validation
        const profileForm = document.getElementById('account-profile-form');
        if (profileForm) {
            const firstName = document.getElementById('first_name');
            const lastName = document.getElementById('last_name');
            const phone = document.getElementById('account_phone');
            const avatar = fileInput;
            const allowedAvatarTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            const allowedAvatarExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            const maxAvatarBytes = 2 * 1024 * 1024;

            function avatarExtension(file) {
                const name = (file && file.name) ? String(file.name) : '';
                const parts = name.split('.');
                return parts.length > 1 ? parts.pop().toLowerCase() : '';
            }

            function isAllowedAvatar(file) {
                if (!file) return true;
                if (file.type && allowedAvatarTypes.indexOf(file.type) !== -1) return true;
                return allowedAvatarExts.indexOf(avatarExtension(file)) !== -1;
            }

            function validateFirstName() {
                const value = (firstName.value || '').trim();
                if (!value) {
                    setFieldError(profileForm, firstName, 'first_name', 'First name is required.');
                    return false;
                }
                if (value.length > 75) {
                    setFieldError(profileForm, firstName, 'first_name', 'First name may not be greater than 75 characters.');
                    return false;
                }
                clearFieldError(profileForm, firstName, 'first_name');
                return true;
            }

            function validateLastName() {
                const value = (lastName.value || '').trim();
                if (!value) {
                    setFieldError(profileForm, lastName, 'last_name', 'Last name is required.');
                    return false;
                }
                if (value.length > 75) {
                    setFieldError(profileForm, lastName, 'last_name', 'Last name may not be greater than 75 characters.');
                    return false;
                }
                clearFieldError(profileForm, lastName, 'last_name');
                return true;
            }

            function validatePhone() {
                const value = (phone.value || '').trim();
                if (value.length > 30) {
                    setFieldError(profileForm, phone, 'phone', 'Phone number may not be greater than 30 characters.');
                    return false;
                }
                clearFieldError(profileForm, phone, 'phone');
                return true;
            }

            function validateAvatar() {
                const file = avatar && avatar.files && avatar.files[0];
                if (!file) {
                    clearFieldError(profileForm, avatar, 'avatar');
                    return true;
                }
                if (!isAllowedAvatar(file)) {
                    setFieldError(profileForm, avatar, 'avatar', 'Please upload a JPG, PNG, GIF, or WEBP image.');
                    return false;
                }
                if (file.size > maxAvatarBytes) {
                    setFieldError(profileForm, avatar, 'avatar', 'The avatar may not be greater than 2MB.');
                    return false;
                }
                clearFieldError(profileForm, avatar, 'avatar');
                return true;
            }

            if (firstName) firstName.addEventListener('input', validateFirstName);
            if (lastName) lastName.addEventListener('input', validateLastName);
            if (phone) phone.addEventListener('input', validatePhone);
            if (avatar && preview) {
                avatar.addEventListener('change', function () {
                    validateAvatar();
                    const file = this.files && this.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.innerHTML = '<img src="' + e.target.result + '" alt="Profile photo" id="account-avatar-image">';
                    };
                    reader.readAsDataURL(file);
                });
            }

            profileForm.addEventListener('submit', function (e) {
                const ok = validateFirstName() && validateLastName() && validatePhone() && validateAvatar();
                if (!ok) {
                    e.preventDefault();
                    e.stopPropagation();
                    focusFirstInvalid(profileForm);
                }
            });
        }

        // Password form validation
        const passwordForm = document.getElementById('account-password-form');
        if (passwordForm) {
            const currentPassword = document.getElementById('current_password');
            const newPassword = document.getElementById('password');
            const confirmPassword = document.getElementById('password_confirmation');
            const minLength = 8;

            function validateCurrent() {
                const value = (currentPassword.value || '').trim();
                if (!value) {
                    setFieldError(passwordForm, currentPassword, 'current_password', 'Current password is required.');
                    return false;
                }
                clearFieldError(passwordForm, currentPassword, 'current_password');
                return true;
            }

            function validateNew() {
                const value = newPassword.value || '';
                if (!value) {
                    setFieldError(passwordForm, newPassword, 'password', 'New password is required.');
                    return false;
                }
                if (value.length < minLength) {
                    setFieldError(passwordForm, newPassword, 'password', 'The password must be at least ' + minLength + ' characters.');
                    return false;
                }
                clearFieldError(passwordForm, newPassword, 'password');
                return true;
            }

            function validateConfirm() {
                const value = confirmPassword.value || '';
                if (!value) {
                    setFieldError(passwordForm, confirmPassword, 'password_confirmation', 'Please confirm your new password.');
                    return false;
                }
                if (value !== newPassword.value) {
                    setFieldError(passwordForm, confirmPassword, 'password_confirmation', 'Password confirmation does not match.');
                    return false;
                }
                clearFieldError(passwordForm, confirmPassword, 'password_confirmation');
                return true;
            }

            [currentPassword, newPassword, confirmPassword].forEach((input) => {
                if (!input) return;
                input.addEventListener('input', function () {
                    if (input === currentPassword) validateCurrent();
                    if (input === newPassword) {
                        validateNew();
                        if (confirmPassword.value) validateConfirm();
                    }
                    if (input === confirmPassword) validateConfirm();
                });
            });

            passwordForm.addEventListener('submit', function (e) {
                const ok = validateCurrent() && validateNew() && validateConfirm();
                if (!ok) {
                    e.preventDefault();
                    e.stopPropagation();
                    focusFirstInvalid(passwordForm);
                }
            });
        }
    })();
</script>
@endpush
