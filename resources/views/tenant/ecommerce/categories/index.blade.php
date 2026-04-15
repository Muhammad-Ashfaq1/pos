@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">E-commerce /</span> Categories
    </h4>

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="row col-12">
                    <div class="col-lg-4 col-md-6 ">
                        <div class="mb-0 position-relative flex-grow-1 w-100">
                            <input type="text" class="form-control pe-5" placeholder="Search products...">
                            <i class="ti tabler-search position-absolute text-muted"
                                style="top: 50%; right: 1rem; transform: translateY(-50%);"></i>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-6 p-0 d-flex justify-content-end align-items-end">
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-primary text-nowrap" data-bs-target="#addCategoryModal"
                                data-bs-toggle="modal">
                                <i class="ti tabler-plus me-1"></i>Add Category
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-operations dataTable mb-0">
                <thead class=" bg-label-primary">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="operationTableBody" class="table-border-bottom-0 scroll-y">
                    @for ($i = 0 ; $i < 10 ; $i++)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>Albert Cook</td>
                            <td class="text-center"><span class="badge bg-label-success me-1">Active</span></td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect view-operation"
                                        data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-primary"
                                        aria-label="View Operation" data-bs-original-title="View Operation"><i
                                            class="icon-base ti tabler-eye"></i></a>
                                    <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-operation"
                                        data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-primary"
                                        aria-label="Edit Operation" data-bs-original-title="Edit Operation"> <i
                                            class="icon-base ti tabler-edit icon-md"></i></a>
                                    <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-operation"
                                        data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-primary"
                                        aria-label="Delete Operation" data-bs-original-title="Delete Operation"> <i
                                            class="icon-base ti tabler-trash icon-md text-danger"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>

    @include('tenant.ecommerce.categories.category-modal')
@endsection
