@extends('layouts.app')

@section('title', 'Demo Requests')

@section('content')
@php
    use App\Enums\DemoRequestStatus;
@endphp

<div class="row g-4">
    <div class="col-12">
        <div class="card bg-label-primary">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <span class="badge bg-primary mb-3">Lead Inbox</span>
                        <h3 class="mb-2">Every "Request a Demo" lead in one place</h3>
                        <p class="text-muted mb-0">
                            Demo requests submitted from the public landing page land here in real time. Track each lead, update its status, and keep your sales follow-up organized.
                        </p>
                    </div>
                    <div class="col-lg-4 text-center d-none d-lg-block">
                        <img src="{{ asset('assets/img/illustrations/card-website-analytics-1.png') }}" alt="Demo requests" class="img-fluid" style="max-height: 170px;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted text-uppercase small fw-semibold d-block mb-1">Total Requests</span>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <span class="avatar avatar-sm">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="icon-base ti tabler-calendar-event"></i>
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted text-uppercase small fw-semibold d-block mb-1">New</span>
                        <h3 class="mb-0 text-warning">{{ $stats['new'] }}</h3>
                    </div>
                    <span class="avatar avatar-sm">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="icon-base ti tabler-bell-ringing"></i>
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted text-uppercase small fw-semibold d-block mb-1">Scheduled</span>
                        <h3 class="mb-0 text-primary">{{ $stats['scheduled'] }}</h3>
                    </div>
                    <span class="avatar avatar-sm">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="icon-base ti tabler-calendar-check"></i>
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted text-uppercase small fw-semibold d-block mb-1">Closed</span>
                        <h3 class="mb-0 text-success">{{ $stats['closed'] }}</h3>
                    </div>
                    <span class="avatar avatar-sm">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="icon-base ti tabler-circle-check"></i>
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">Demo Request Directory</h5>
                    <p class="text-muted mb-0">Leads captured from the public landing page</p>
                </div>
            </div>

            <div class="card-datatable table-responsive pt-0">
                <table class="datatables-basic table" id="demo-requests-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Contact</th>
                            <th>Email / Phone</th>
                            <th>Business Type</th>
                            <th>Status</th>
                            <th>Received</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $demoRequest)
                            @php $status = $demoRequest->status; @endphp
                            <tr>
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
                                    <button type="button"
                                        class="btn btn-sm btn-primary manage-demo-btn"
                                        data-id="{{ $demoRequest->id }}"
                                        data-name="{{ $demoRequest->name }}"
                                        data-business="{{ $demoRequest->business_name }}"
                                        data-email="{{ $demoRequest->email }}"
                                        data-phone="{{ $demoRequest->phone }}"
                                        data-type="{{ $demoRequest->business_type }}"
                                        data-message="{{ $demoRequest->message }}"
                                        data-status="{{ $status->value }}"
                                        data-notes="{{ $demoRequest->admin_notes }}">
                                        <i class="icon-base ti tabler-eye me-1"></i>Manage
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="demoRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="demoModalName">-</h5>
                    <p class="text-muted mb-0 small" id="demoModalBusiness">-</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted text-uppercase small fw-semibold mb-1">Email</div>
                            <div id="demoModalEmail">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted text-uppercase small fw-semibold mb-1">Phone</div>
                            <div id="demoModalPhone">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted text-uppercase small fw-semibold mb-1">Business Type</div>
                            <div id="demoModalType">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted text-uppercase small fw-semibold mb-1">Current Status</div>
                            <div id="demoModalStatusBadge">-</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3">
                            <div class="text-muted text-uppercase small fw-semibold mb-1">Message</div>
                            <div id="demoModalMessage" class="text-muted">-</div>
                        </div>
                    </div>
                </div>

                <form id="demoStatusForm">
                    <input type="hidden" id="demoRequestId" value="">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label" for="demoStatusSelect">Update Status</label>
                            <select class="form-select" id="demoStatusSelect">
                                @foreach (DemoRequestStatus::cases() as $case)
                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label" for="demoNotes">Internal Notes</label>
                            <textarea class="form-control" id="demoNotes" rows="2" placeholder="Add a follow-up note for your team"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="demoSaveBtn">
                    <i class="icon-base ti tabler-device-floppy me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    const $modalEl = $('#demoRequestModal');
    const modal = $modalEl.length ? bootstrap.Modal.getOrCreateInstance($modalEl[0]) : null;
    const token = $('meta[name="csrf-token"]').attr('content');

    const table = $('#demo-requests-table').DataTable({
        responsive: true,
        order: [[5, 'desc']],
        language: {
            search: '',
            searchPlaceholder: 'Search name, business, email or phone'
        },
        columnDefs: [
            { orderable: false, targets: [4, 6] }
        ]
    });

    $(document).on('click', '.manage-demo-btn', function () {
        const $btn = $(this);
        $('#demoRequestId').val($btn.data('id'));
        $('#demoModalName').text($btn.data('name') || '-');
        $('#demoModalBusiness').text($btn.data('business') || 'No business name provided');
        $('#demoModalEmail').text($btn.data('email') || '-');
        $('#demoModalPhone').text($btn.data('phone') || '-');
        $('#demoModalType').text($btn.data('type') || '—');
        $('#demoModalMessage').text($btn.data('message') || 'No message provided.');
        $('#demoStatusSelect').val(String($btn.data('status')));
        $('#demoNotes').val($btn.data('notes') || '');

        const badge = $btn.closest('tr').find('.status-badge');
        $('#demoModalStatusBadge').html('<span class="badge ' + (badge.attr('class') || '').replace('status-badge', '').trim() + '">' + badge.text() + '</span>');

        if (modal) modal.show();
    });

    $('#demoSaveBtn').on('click', function () {
        const id = $('#demoRequestId').val();
        const $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: '/admin/demo-requests/' + id + '/status',
            type: 'POST',
            data: {
                _token: token,
                status: $('#demoStatusSelect').val(),
                admin_notes: $('#demoNotes').val()
            },
            success: function (response) {
                if (response.success && typeof window.appNotify === 'function') {
                    window.appNotify('success', response.message);
                }
                if (modal) modal.hide();
                setTimeout(function () { window.location.reload(); }, 600);
            },
            error: function (xhr) {
                if (typeof window.appNotify === 'function') {
                    window.appNotify('error', xhr.responseJSON?.message || 'Update failed.');
                }
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>
@endsection
