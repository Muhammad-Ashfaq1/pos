<form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data" class="account-settings-form" id="account-profile-form">
    @csrf
    <input
        type="file"
        id="account_avatar"
        name="avatar"
        class="d-none @error('avatar') is-invalid @enderror"
        accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">

    <div class="account-settings-header">
        <label class="account-settings-avatar" for="account_avatar" id="account-avatar-preview" title="Change profile photo">
            @if (! empty($accountAvatarUrl))
                <img src="{{ $accountAvatarUrl }}" alt="{{ $account->name }}" id="account-avatar-image">
            @else
                <span id="account-avatar-initial">{{ strtoupper(substr($account->name ?? 'U', 0, 1)) }}</span>
            @endif
        </label>
        <div class="account-settings-header-text">
            <h4 class="account-settings-title">{{ $account->name }}</h4>
            <p class="account-settings-subtitle">Update your profile information and settings.</p>
            @error('avatar')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="row g-4 account-settings-fields">
        <div class="col-md-6">
            <label class="form-label" for="first_name">
                First Name <span class="required-mark">*</span>
            </label>
            <input
                type="text"
                id="first_name"
                name="first_name"
                class="form-control @error('first_name') is-invalid @enderror"
                value="{{ $accountFirstName }}"
                required
                autofocus>
            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="last_name">
                Last Name <span class="required-mark">*</span>
            </label>
            <input
                type="text"
                id="last_name"
                name="last_name"
                class="form-control @error('last_name') is-invalid @enderror"
                value="{{ $accountLastName }}"
                required>
            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="account_email">
                Email <span class="required-mark">*</span>
            </label>
            <input type="email" id="account_email" class="form-control" value="{{ $account->email }}" disabled>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="account_phone">Phone Number</label>
            <input
                type="text"
                id="account_phone"
                name="phone"
                class="form-control @error('phone') is-invalid @enderror"
                value="{{ old('phone', $account->phone) }}"
                placeholder="Enter phone number">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="account-settings-actions">
        <button type="submit" class="btn btn-primary account-settings-save-btn">Save Changes</button>
    </div>
</form>
