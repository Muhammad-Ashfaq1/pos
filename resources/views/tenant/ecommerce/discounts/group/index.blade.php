@extends('layouts.app')

@section('title', 'Discount Groups')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Discount Groups</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Discount Groups</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Customer discount group</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDiscountGroupModal">
                <i class="ti tabler-plus me-1"></i> Add New group
            </button>
        </div>
        <div class="card-datatable table-responsive p-5">
            <table class="table" id="discountGroupsTable" data-delete-url-pattern="{{ route('tenant.discounts.group.delete', ':id') }}">
                <thead class="bg-label-primary">
                    <tr>
                        <th>Title Name</th>
                        <th>Slug</th>
                        <th>Discount Value</th>
                        <th>Type</th>
                        <th>Is Active</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="discount-groups-body">
                    @foreach ($discountGroups as $group)
                        <tr>
                            <td>{{ $group->name }}</td>
                            <td>{{ $group->slug }}</td>
                            <td>{{ $group->type === 'percentage' ? $group->value . '%' : '$' . $group->value }}</td>
                            <td>{{ $group->type }}</td>
                            <td>
                                @if ($group->is_active)
                                    <span class="badge bg-label-success">Yes</span>
                                @else
                                    <span class="badge bg-label-danger">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <a href="javascript:void(0);" class="text-primary edit-discount-group" 
                                        data-id="{{ $group->id }}"
                                        data-title="{{ $group->name }}"
                                        data-type="{{ $group->type }}"
                                        data-value="{{ $group->value }}"
                                    ><i class="ti tabler-edit"></i></a>
                                    <a href="javascript:void(0);" class="text-danger delete-discount-group" 
                                        data-id="{{ $group->id }}"
                                        data-url="{{ route('tenant.discounts.group.delete', $group->id) }}"
                                    ><i class="ti tabler-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>



    @include('tenant.ecommerce.discounts.group.add-discount-modal')
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/tenant/discount-groups.js') }}"></script>
@endsection
