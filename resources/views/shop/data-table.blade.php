@foreach($shops as $index => $shop)
@php($status = $shop->status instanceof \App\Enums\TenantStatus ? $shop->status->value : $shop->status)
@php($phone = $shop->owner_phone ?: $shop->phone)
<tr>
    <td>{{ $index + 1 }}</td>
    <td class="fw-medium">{{ $shop->owner_name ?: '—' }}</td>
    <td>{{ $shop->owner_email_address ?: '—' }}</td>
    <td>{{ $phone ?: '—' }}</td>
    <td class="fw-semibold">{{ $shop->display_name ?: '—' }}</td>
    <td>
        @if($shop->plan)
            <span class="badge bg-label-primary mb-1">{{ $shop->plan->name }}</span>
            @if($shop->plan_expires_at)
                <small class="text-muted d-block">Exp: {{ $shop->plan_expires_at->format('M j, Y') }}</small>
            @endif
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td>
        <span class="badge bg-{{ $shop->status->badgeClass() }}">
            {{ $shop->status->label() }}
        </span>
    </td>
    <td class="text-center">
        @if($status === 'approved')
            <button type="button" class="btn btn-sm btn-icon btn-label-warning shop-impersonate-btn" data-id="{{ $shop->id }}" data-shop-name="{{ $shop->display_name ?: 'this shop' }}" title="Impersonate">
                <i class="icon-base ti tabler-login-2"></i>
            </button>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td class="text-center">
        <div class="d-inline-flex gap-1">
            @if($status === 'pending')
                <button type="button" class="btn btn-sm btn-icon btn-outline-success shop-action-btn" data-id="{{ $shop->id }}" data-action="approve" title="Approve"><i class="icon-base ti tabler-check"></i></button>
                <button type="button" class="btn btn-sm btn-icon btn-outline-danger shop-action-btn" data-id="{{ $shop->id }}" data-action="reject" title="Reject"><i class="icon-base ti tabler-x"></i></button>
            @endif
            @if($status === 'approved')
                <button type="button" class="btn btn-sm btn-icon btn-outline-primary edit-shop-btn" data-id="{{ $shop->id }}" title="Edit"><i class="icon-base ti tabler-edit"></i></button>
                <button type="button" class="btn btn-sm btn-icon btn-outline-secondary shop-action-btn" data-id="{{ $shop->id }}" data-action="deactivate" title="Deactivate"><i class="icon-base ti tabler-power"></i></button>
            @endif
            @if($status === 'inactive')
                <button type="button" class="btn btn-sm btn-icon btn-outline-success shop-action-btn" data-id="{{ $shop->id }}" data-action="activate" title="Activate"><i class="icon-base ti tabler-power"></i></button>
            @endif
            @if($status === 'rejected')
                <button type="button" class="btn btn-sm btn-icon btn-outline-success shop-action-btn" data-id="{{ $shop->id }}" data-action="approve" title="Approve"><i class="icon-base ti tabler-check"></i></button>
            @endif
            @if($status === 'suspended')
                <button type="button" class="btn btn-sm btn-icon btn-outline-success shop-action-btn" data-id="{{ $shop->id }}" data-action="reactivate" title="Reactivate"><i class="icon-base ti tabler-rotate-clockwise"></i></button>
            @endif
        </div>
    </td>
</tr>
@endforeach
