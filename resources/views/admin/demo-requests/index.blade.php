@extends('layouts.app')

@section('title', 'Demo Requests')

@push('styles')
    @include('partials.pos-listing-assets')
@endpush

@section('content')
@php
    use App\Enums\DemoRequestStatus;
@endphp

<div class="row g-4">
    <div class="col-12">
        <div class="pos-glass-card pos-tone-primary">
            <div class="pos-glass-intro">
                <div class="pos-glass-intro-copy">
                    <h4 class="pos-glass-intro-title">Every "Request a Demo" lead in one place</h4>
                    <p class="pos-glass-intro-subtitle">
                        Demo requests submitted from the public landing page land here in real time. Track each lead, update its status, and keep your sales follow-up organized.
                    </p>
                </div>
                <div class="pos-glass-intro-actions d-flex flex-wrap gap-2 align-items-center">
                    <span class="pos-glass-pill pos-tone-primary">
                        <i class="icon-base ti tabler-calendar-event" aria-hidden="true"></i>
                        {{ $stats['total'] }} requests
                    </span>
                    @if ($stats['new'] > 0)
                        <span class="pos-glass-pill pos-tone-warning">
                            <i class="icon-base ti tabler-bell-ringing" aria-hidden="true"></i>
                            {{ $stats['new'] }} new
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="pos-glass-card pos-tone-primary h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-calendar-event" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Total Requests</h6>
                </div>
                <p class="pos-stat-value">{{ $stats['total'] }}</p>
                <p class="pos-stat-desc mb-0">Platform demo submissions</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="pos-glass-card pos-tone-warning h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-bell-ringing" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">New Leads</h6>
                </div>
                <p class="pos-stat-value text-warning">{{ $stats['new'] }}</p>
                <p class="pos-stat-desc mb-0">Awaiting contact</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="pos-glass-card pos-tone-info h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-calendar-check" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Scheduled</h6>
                </div>
                <p class="pos-stat-value text-info">{{ $stats['scheduled'] }}</p>
                <p class="pos-stat-desc mb-0">Demo meeting booked</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="pos-glass-card pos-tone-success h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-circle-check" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Closed</h6>
                </div>
                <p class="pos-stat-value text-success">{{ $stats['closed'] }}</p>
                <p class="pos-stat-desc mb-0">Completed / converted</p>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="pos-listing">
            <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
                <div class="pos-listing-toolbar">
                    <h4 class="pos-listing-title">Demo Request Directory</h4>
                    <div class="pos-listing-search-slot">
                        <div class="dt-search">
                            <input
                                type="search"
                                id="demoTableSearch"
                                class="form-control"
                                placeholder="Search name, business, email or phone"
                                autocomplete="off"
                            >
                        </div>
                    </div>
                </div>

                <div class="card-datatable table-responsive pos-listing-table pt-0">
                    <table class="demo-requests-datatables table table-hover align-middle mb-0" id="demo-requests-table">
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
                        <tbody id="demo-requests-table-body">
                            @include('admin.demo-requests.data-table')
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal fade pos-listing-modal" id="demoRequestModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        window.adminDemoRequests = {
            statusUrl: @json(url('/admin/demo-requests/__ID__/status')),
            csrfToken: @json(csrf_token())
        };
    </script>
    <script src="{{ asset('assets/js/pos-listing-toolbar.js') }}?v={{ filemtime(public_path('assets/js/pos-listing-toolbar.js')) }}"></script>
    <script src="{{ asset('assets/js/admin/demo-requests.js') }}?v={{ filemtime(public_path('assets/js/admin/demo-requests.js')) }}"></script>
@endsection
