@extends($accountSettingsLayout)

@section('title', ($accountSettingsActive ?? 'profile') === 'password' ? 'Change Password' : 'Profile')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/account-settings.css') }}?v={{ filemtime(public_path('assets/css/account-settings.css')) }}">
@endpush

@section('content')
    <div class="account-settings-card" id="account-settings"
         data-profile-url="{{ route('account.profile') }}"
         data-password-url="{{ route('account.password') }}"
         data-active="{{ $accountSettingsActive ?? 'profile' }}">
        @include('account-settings.partials.nav')

        <div class="account-settings-panel"
             data-panel="profile"
             @if (($accountSettingsActive ?? 'profile') !== 'profile') hidden @endif>
            @include('account-settings.partials.profile-form')
        </div>

        <div class="account-settings-panel"
             data-panel="password"
             @if (($accountSettingsActive ?? '') !== 'password') hidden @endif>
            @include('account-settings.partials.password-form')
        </div>
    </div>
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

        // Avatar preview
        const fileInput = document.getElementById('account_avatar');
        const preview = document.getElementById('account-avatar-preview');
        if (fileInput && preview) {
            fileInput.addEventListener('change', function () {
                const file = this.files && this.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Profile photo" id="account-avatar-image">';
                };
                reader.readAsDataURL(file);
            });
        }

        // Password visibility toggles
        root.querySelectorAll('.form-password-toggle').forEach((container) => {
            const toggleBtn = container.querySelector('.input-group-text');
            const input = container.querySelector('input');
            const icon = container.querySelector('i');
            if (!input || !toggleBtn || !icon) return;

            const toggleVisibility = (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('tabler-eye-off');
                    icon.classList.add('tabler-eye');
                } else {
                    input.type = 'password';
                    icon.classList.remove('tabler-eye');
                    icon.classList.add('tabler-eye-off');
                }
            };

            toggleBtn.addEventListener('click', toggleVisibility);
            icon.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleVisibility(e);
            }, true);
        });
    })();
</script>
@endpush
