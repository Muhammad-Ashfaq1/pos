@forelse($shops as $index => $shop)
@php($status = $shop->status instanceof \App\Enums\TenantStatus ? $shop->status->value : $shop->status)
<tr>
    <td>{{ $index + 1 }}</td>

    
    <td>{{ $shop->owner_name ?? '-' }}</td>

    
    <td>{{ $shop->email ?? '-' }}</td>

    
    <td>{{ $shop->shop_name ?? '-' }}</td>

    {{-- ================= STATUS ================= --}}
    <td>
        @if($status == 'pending')
            <span class="badge bg-warning text-dark status-badge">Pending</span>

        @elseif($status == 'approved')
            <span class="badge bg-success status-badge">Approved</span>

        @elseif($status == 'rejected')
            <span class="badge bg-danger status-badge">Rejected</span>

        @elseif($status == 'suspended')
            <span class="badge bg-secondary status-badge">Suspended</span>

        @else
            <span class="badge bg-dark status-badge">Unknown</span>
        @endif
    </td>

    {{-- ================= IMPERSONATE ================= --}}
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

    {{-- ================= ACTION ================= --}}
    <td>

        {{-- PENDING --}}
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


        {{-- APPROVED --}}
        @if($status == 'approved')

            <button class="btn btn-warning btn-sm action-btn"
                data-id="{{ $shop->id }}"
                data-action="suspend">
                Suspend
            </button>

        @endif


        {{-- REJECTED --}}
        @if($status == 'rejected')

            <button class="btn btn-success btn-sm action-btn"
                data-id="{{ $shop->id }}"
                data-action="approve">
                Approve
            </button>

        @endif


        {{-- SUSPENDED --}}
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
