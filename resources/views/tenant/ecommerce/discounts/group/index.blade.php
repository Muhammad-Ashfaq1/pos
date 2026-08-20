@extends('layouts.app')

@section('title', 'Discount Groups')

@push('styles')
    @include('partials.pos-listing-assets')
@endpush

@section('content')
    <div class="pos-listing">
        <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
            <div class="pos-listing-toolbar">
                <h4 class="pos-listing-title">Discount Groups</h4>
                <div class="pos-listing-toolbar-tools">
                    <div class="pos-listing-toolbar-actions">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDiscountGroupModal">
                            <i class="ti tabler-plus me-1"></i> Add New group
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-datatable table-responsive pos-listing-table pt-0">
                <table class="table table-hover align-middle" id="discountGroupsTable" data-delete-url-pattern="{{ route('tenant.discounts.group.delete', ':id') }}">
                    <thead>
                        <tr>
                            <th>Title Name</th>
                            <th>Slug</th>
                            <th>Discount Value</th>
                            <th>Type</th>
                            <th>Min Limit</th>
                            <th>Earns Credit</th>
                            <th>Is Active</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="discount-groups-body">
                        @foreach ($discountGroups as $group)
                            <tr>
                                <td>{{ $group->name }}</td>
                                <td>{{ $group->slug }}</td>
                                <td>{{ $group->type === 'percentage' ? $group->value . '%' : \App\Support\Currency::format($group->value) }}</td>
                                <td>{{ $group->type }}</td>
                                <td>{{ $group->type === 'fixed' ? \App\Support\Currency::format($group->min_limit) : '-' }}</td>
                                <td>
                                    @if ($group->earns_credit)
                                        <span class="badge rounded bg-label-info">{{ $group->credit_earn_type === 'percentage' ? rtrim(rtrim(number_format((float) $group->credit_earn_rate, 2), '0'), '.') . '%' : \App\Support\Currency::format($group->credit_earn_rate) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($group->is_active)
                                        <span class="badge rounded bg-label-success">Yes</span>
                                    @else
                                        <span class="badge rounded bg-label-danger">No</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-primary edit-discount-group"
                                            data-id="{{ $group->id }}"
                                            data-title="{{ $group->name }}"
                                            data-type="{{ $group->type }}"
                                            data-value="{{ $group->value }}"
                                            data-min-value="{{ $group->min_limit }}"
                                            data-is-active="{{ $group->is_active }}"
                                            data-earns-credit="{{ $group->earns_credit ? 1 : 0 }}"
                                            data-credit-earn-type="{{ $group->credit_earn_type }}"
                                            data-credit-earn-rate="{{ $group->credit_earn_rate }}"
                                            data-credit-min-spend="{{ $group->credit_min_spend }}"
                                            title="Edit"
                                        ><i class="icon-base ti tabler-edit icon-md"></i></button>
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger delete-discount-group"
                                            data-id="{{ $group->id }}"
                                            data-url="{{ route('tenant.discounts.group.delete', $group->id) }}"
                                            title="Delete"
                                        ><i class="icon-base ti tabler-trash icon-md"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @include('tenant.ecommerce.discounts.group.add-discount-modal')
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/tenant/discount-groups.js') }}?v={{ filemtime(public_path('assets/js/tenant/discount-groups.js')) }}"></script>
@endsection
