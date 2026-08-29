@foreach ($requests as $demoRequest)
    @php $status = $demoRequest->status; @endphp
    <tr data-status="{{ $status->value }}">
        <td>{{ $loop->iteration }}</td>
        <td>
            <div class="fw-medium">{{ $demoRequest->name }}</div>
            <small class="text-muted">{{ $demoRequest->business_name ?: '—' }}</small>
        </td>
        <td>
            <div><a href="mailto:{{ $demoRequest->email }}">{{ $demoRequest->email }}</a></div>
            <small class="text-muted">{{ $demoRequest->phone ?: '—' }}</small>
        </td>
        <td>{{ $demoRequest->business_type ?: '—' }}</td>
        <td>
            <span class="badge bg-label-{{ $status->badgeClass() }} status-badge">{{ $status->label() }}</span>
        </td>
        <td data-order="{{ $demoRequest->created_at->timestamp }}">
            <span>{{ $demoRequest->created_at->format('M d, Y') }}</span>
            <small class="text-muted d-block">{{ $demoRequest->created_at->diffForHumans() }}</small>
        </td>
        <td class="text-center">
            <button
                type="button"
                class="btn btn-sm btn-outline-primary manage-demo-btn"
                data-id="{{ $demoRequest->id }}"
                data-name="{{ $demoRequest->name }}"
                data-business="{{ $demoRequest->business_name }}"
                data-email="{{ $demoRequest->email }}"
                data-phone="{{ $demoRequest->phone }}"
                data-type="{{ $demoRequest->business_type }}"
                data-message="{{ $demoRequest->message }}"
                data-status="{{ $status->value }}"
                data-notes="{{ $demoRequest->admin_notes }}"
                title="Manage"
            >
                <i class="icon-base ti tabler-eye me-1"></i>Manage
            </button>
        </td>
    </tr>
@endforeach
