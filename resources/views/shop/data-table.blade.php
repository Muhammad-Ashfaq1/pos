@forelse($shops as $index => $shop)
@php($status = $shop->status instanceof \App\Enums\TenantStatus ? $shop->status->value : $shop->status)
<tr>
    <td>{{ $index + 1 }}</td>
    <td>{{ $shop->owner_name ?? '-' }}</td>
    <td>{{ $shop->owner_email_address ?? '-' }}</td>
    <td>{{ $shop->display_name ?? '-' }}</td>
    <td>
        <span class="badge bg-{{ $shop->status->badgeClass() }} status-badge">
            {{ ucfirst($status) }}
        </span>
    </td>
    <td>
        @if($status == 'approved')
            <button onclick="confirmImpersonate({{ $shop->id }})"
                class="btn btn-warning btn-sm">
                Impersonate
            </button>
        @else
            <button class="btn btn-secondary btn-sm" disabled>
                Not Allowed
            </button>
        @endif
    </td>
    <td>
        @if($status == 'pending')
            <button class="btn btn-success btn-sm action-btn"
                data-id="{{ $shop->id }}"
                data-action="approve">
                Approve
            </button>

            <button class="btn btn-danger btn-sm action-btn"
                data-id="{{ $shop->id }}"
                data-action="reject">
                Reject
            </button>
        @endif
        @if($status == 'approved')
            <button class="btn btn-warning btn-sm action-btn"
                data-id="{{ $shop->id }}"
                data-action="suspend">
                Suspend
            </button>
        @endif
        @if($status == 'rejected')
            <button class="btn btn-success btn-sm action-btn"
                data-id="{{ $shop->id }}"
                data-action="approve">
                Approve
            </button>
        @endif
        @if($status == 'suspended')
            <button class="btn btn-success btn-sm action-btn"
                data-id="{{ $shop->id }}"
                data-action="reactivate">
                Reactivate
            </button>
        @endif
    </td>
</tr>

@empty
@endforelse
