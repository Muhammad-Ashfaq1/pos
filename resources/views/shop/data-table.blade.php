@forelse($shops as $index => $shop)
@php($status = $shop->status instanceof \App\Enums\TenantStatus ? $shop->status->value : $shop->status)
<tr>
    <td>{{ $index + 1 }}</td>
    <td>
        <div class="d-flex align-items-center gap-3">
            <span class="avatar avatar-sm">
                <span class="avatar-initial rounded bg-label-primary">
                    {{ strtoupper(substr($shop->owner_name ?? 'S', 0, 1)) }}
                </span>
            </span>
            <div>
                <div class="fw-semibold text-body">{{ $shop->owner_name ?? 'Unknown Owner' }}</div>
                <small class="text-muted">Tenant ID: #{{ $shop->id }}</small>
            </div>
        </div>
    </td>
    <td>
        <div class="d-flex flex-column">
            <span class="fw-medium">{{ $shop->owner_email_address ?? '-' }}</span>
            <small class="text-muted">{{ $shop->owner_phone ?? $shop->phone ?? 'No phone added' }}</small>
        </div>
    </td>
    <td>
        <div class="d-flex flex-column">
            <span class="fw-semibold">{{ $shop->display_name ?? '-' }}</span>
            <small class="text-muted">{{ $shop->city ? $shop->city . ', ' . $shop->country : ($shop->country ?? 'Location not added') }}</small>
        </div>
    </td>
    <td>
        <div class="d-flex flex-column gap-1">
            <span class="badge bg-{{ $shop->status->badgeClass() }} status-badge align-self-start">
                {{ ucfirst($status) }}
            </span>
            @if($status === 'pending')
                <small class="text-warning">Awaiting review</small>
            @elseif($status === 'approved')
                <small class="text-success">Login enabled</small>
            @elseif($status === 'suspended')
                <small class="text-muted">Access paused</small>
            @elseif($status === 'rejected')
                <small class="text-danger">Registration declined</small>
            @endif
        </div>
    </td>
    <td>
        @if($status == 'approved')
            <button onclick="confirmImpersonate({{ $shop->id }})"
                class="btn btn-label-warning btn-sm">
                <i class="icon-base ti tabler-login-2 me-1"></i>Impersonate
            </button>
        @else
            <button class="btn btn-secondary btn-sm" disabled>
                Not Allowed
            </button>
        @endif
    </td>
    <td class="text-center">
        <div class="d-flex justify-content-center flex-wrap gap-2">
        @if($status == 'pending')
            <button class="btn btn-success btn-sm action-btn"
                data-id="{{ $shop->id }}"
                data-action="approve">
                <i class="icon-base ti tabler-check me-1"></i>Approve
            </button>

            <button class="btn btn-danger btn-sm action-btn"
                data-id="{{ $shop->id }}"
                data-action="reject">
                <i class="icon-base ti tabler-x me-1"></i>Reject
            </button>
        @endif
        @if($status == 'approved')
            <button class="btn btn-warning btn-sm action-btn"
                data-id="{{ $shop->id }}"
                data-action="suspend">
                <i class="icon-base ti tabler-player-pause me-1"></i>Suspend
            </button>
        @endif
        @if($status == 'rejected')
            <button class="btn btn-success btn-sm action-btn"
                data-id="{{ $shop->id }}"
                data-action="approve">
                <i class="icon-base ti tabler-refresh me-1"></i>Approve
            </button>
        @endif
        @if($status == 'suspended')
            <button class="btn btn-success btn-sm action-btn"
                data-id="{{ $shop->id }}"
                data-action="reactivate">
                <i class="icon-base ti tabler-rotate-clockwise me-1"></i>Reactivate
            </button>
        @endif
        </div>
    </td>
</tr>

@empty
<tr>
    <td colspan="7" class="text-center py-5">
        <div class="d-flex flex-column align-items-center justify-content-center">
            <span class="avatar avatar-xl mb-3">
                <span class="avatar-initial rounded bg-label-primary">
                    <i class="icon-base ti tabler-building-store icon-lg"></i>
                </span>
            </span>
            <h6 class="mb-1">No shops found</h6>
            <p class="text-muted mb-0">New tenant registrations will appear here for review.</p>
        </div>
    </td>
</tr>
@endforelse
