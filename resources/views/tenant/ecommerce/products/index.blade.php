@extends('layouts.app')

@section('title', 'Sub Categories')

@section('content')
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">E-commerce /</span> Sub Categories
    </h4>

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="row col-12">
                    <div class="col-lg-4">
                        <div class="mb-0 position-relative flex-grow-1 w-100">
                            <input type="text" class="form-control pe-5" placeholder="Search products...">
                            <i class="ti tabler-search position-absolute text-muted"
                                style="top: 50%; right: 1rem; transform: translateY(-50%);"></i>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-6 d-flex justify-content-end align-items-end">
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-primary text-nowrap" data-bs-target="#addProductModal"
                                data-bs-toggle="modal">
                                <i class="ti tabler-plus me-1"></i> Add Product
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
                            <th>WO #</th>
                            <th>Job Name</th>
                            <th class="operations-table-header-wrap">Property Name</th>
                            <th>Account</th>
                            <th class="operations-table-header-wrap">Account Owner</th>
                            <th class="operations-table-header-wrap">Operations Manager</th>
                            <th class="operations-table-header-wrap">Schedule Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="operationTableBody" class="table-border-bottom-0 scroll-y">
                        <tr>
                            <td>123456</td>
                            <td>Albert Cook</td>
                            <td>Albert Cook</td>
                            <td><p class="mb-0">John Doe</p></td>
                            <td>
                                <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center gap-2">
                                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                        class="avatar avatar-sm pull-up" title="Lilian Fuller">
                                        <img src="../../assets/img/avatars/5.png" alt="Avatar" class="rounded-circle">
                                    </li>
                                    <p class="mb-0">John Doe</p>
                                </ul>
                            </td>
                            <td>
                                <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center gap-2">
                                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                        class="avatar avatar-sm pull-up" title="Lilian Fuller">
                                        <img src="../../assets/img/avatars/5.png" alt="Avatar" class="rounded-circle">
                                    </li>
                                    <p class="mb-0">John Doe</p>
                                </ul>
                            </td>
                            <td>5/2/2026</td>
                            <td class="text-center"><span class="badge bg-label-primary me-1">Active</span></td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect view-operation"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="tooltip-primary" aria-label="View Operation"
                                        data-bs-original-title="View Operation"><i class="icon-base ti tabler-eye"></i></a>
                                    <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-operation"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="tooltip-primary" aria-label="Edit Operation"
                                        data-bs-original-title="Edit Operation"> <i
                                            class="icon-base ti tabler-edit icon-md"></i></a>
                                    <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-operation"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="tooltip-primary" aria-label="Delete Operation"
                                        data-bs-original-title="Delete Operation"> <i
                                            class="icon-base ti tabler-trash icon-md text-danger"></i></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>123456</td>
                            <td>Albert Cook</td>
                            <td>Albert Cook</td>
                            <td><p class="mb-0">John Doe</p></td>
                            <td>
                                <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center gap-2">
                                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                        class="avatar avatar-sm pull-up" title="Lilian Fuller">
                                        <img src="../../assets/img/avatars/5.png" alt="Avatar" class="rounded-circle">
                                    </li>
                                    <p class="mb-0">John Doe</p>
                                </ul>
                            </td>
                            <td>
                                <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center gap-2">
                                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                        class="avatar avatar-sm pull-up" title="Lilian Fuller">
                                        <img src="../../assets/img/avatars/5.png" alt="Avatar" class="rounded-circle">
                                    </li>
                                    <p class="mb-0">John Doe</p>
                                </ul>
                            </td>
                            <td>5/2/2026</td>
                            <td class="text-center"><span class="badge bg-label-success me-1">Completed</span></td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect view-operation"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="tooltip-primary" aria-label="View Operation"
                                        data-bs-original-title="View Operation"><i
                                            class="icon-base ti tabler-eye"></i></a>
                                    <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-operation"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="tooltip-primary" aria-label="Edit Operation"
                                        data-bs-original-title="Edit Operation"> <i
                                            class="icon-base ti tabler-edit icon-md"></i></a>
                                    <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-operation"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="tooltip-primary" aria-label="Delete Operation"
                                        data-bs-original-title="Delete Operation"> <i
                                            class="icon-base ti tabler-trash icon-md text-danger"></i></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>123456</td>
                            <td>Albert Cook</td>
                            <td>Albert Cook</td>
                            <td><p class="mb-0">John Doe</p></td>
                            <td>
                                <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center gap-2">
                                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                        class="avatar avatar-sm pull-up" title="Lilian Fuller">
                                        <img src="../../assets/img/avatars/5.png" alt="Avatar" class="rounded-circle">
                                    </li>
                                    <p class="mb-0">John Doe</p>
                                </ul>
                            </td>
                            <td>
                                <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center gap-2">
                                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                        class="avatar avatar-sm pull-up" title="Lilian Fuller">
                                        <img src="../../assets/img/avatars/5.png" alt="Avatar" class="rounded-circle">
                                    </li>
                                    <p class="mb-0">John Doe</p>
                                </ul>
                            </td>
                            <td>5/2/2026</td>
                            <td class="text-center"><span class="badge bg-label-warning me-1">Pending</span></td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect view-operation"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="tooltip-primary" aria-label="View Operation"
                                        data-bs-original-title="View Operation"><i
                                            class="icon-base ti tabler-eye"></i></a>
                                    <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-operation"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="tooltip-primary" aria-label="Edit Operation"
                                        data-bs-original-title="Edit Operation"> <i
                                            class="icon-base ti tabler-edit icon-md"></i></a>
                                    <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-operation"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="tooltip-primary" aria-label="Delete Operation"
                                        data-bs-original-title="Delete Operation"> <i
                                            class="icon-base ti tabler-trash icon-md text-danger"></i></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>123456</td>
                            <td>Albert Cook</td>
                            <td>Albert Cook</td>
                            <td><p class="mb-0">John Doe</p></td>
                            <td>
                                <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center gap-2">
                                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                        class="avatar avatar-sm pull-up" title="Lilian Fuller">
                                        <img src="../../assets/img/avatars/5.png" alt="Avatar" class="rounded-circle">
                                    </li>
                                    <p class="mb-0">John Doe</p>
                                </ul>
                            </td>
                            <td>
                                <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center gap-2">
                                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                        class="avatar avatar-sm pull-up" title="Lilian Fuller">
                                        <img src="../../assets/img/avatars/5.png" alt="Avatar" class="rounded-circle">
                                    </li>
                                    <p class="mb-0">John Doe</p>
                                </ul>
                            </td>
                            <td>5/2/2026</td>
                            <td class="text-center"><span class="badge bg-label-danger me-1">Cancelled</span></td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect view-operation"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="tooltip-primary" aria-label="View Operation"
                                        data-bs-original-title="View Operation"><i
                                            class="icon-base ti tabler-eye"></i></a>
                                    <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-operation"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="tooltip-primary" aria-label="Edit Operation"
                                        data-bs-original-title="Edit Operation"> <i
                                            class="icon-base ti tabler-edit icon-md"></i></a>
                                    <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-operation"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="tooltip-primary" aria-label="Delete Operation"
                                        data-bs-original-title="Delete Operation"> <i
                                            class="icon-base ti tabler-trash icon-md text-danger"></i></a>
                                </div>
                            </td>
                        </tr>
                        @for ($i = 0; $i < 1; $i++)
                            <tr>
                                <td>123456</td>
                                <td>Albert Cook</td>
                                <td>Albert Cook</td>
                                <td><p class="mb-0">John Doe</p></td>
                                <td>
                                    <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center gap-2">
                                        <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                            class="avatar avatar-sm pull-up" title="Lilian Fuller">
                                            <img src="../../assets/img/avatars/5.png" alt="Avatar"
                                                class="rounded-circle">
                                        </li>
                                        <p class="mb-0">John Doe</p>
                                    </ul>
                                </td>
                                <td>
                                    <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center gap-2">
                                        <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                            class="avatar avatar-sm pull-up" title="Lilian Fuller">
                                            <img src="../../assets/img/avatars/5.png" alt="Avatar"
                                                class="rounded-circle">
                                        </li>
                                        <p class="mb-0">John Doe</p>
                                    </ul>
                                </td>
                                <td>5/2/2026</td>
                                <td class="text-center"><span class="badge bg-label-info me-1">In Progress</span></td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                            class="btn btn-icon btn-text-secondary rounded-pill waves-effect view-operation"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-custom-class="tooltip-primary" aria-label="View Operation"
                                            data-bs-original-title="View Operation"><i
                                                class="icon-base ti tabler-eye"></i></a>
                                        <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                            class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-operation"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-custom-class="tooltip-primary" aria-label="Edit Operation"
                                            data-bs-original-title="Edit Operation"> <i
                                                class="icon-base ti tabler-edit icon-md"></i></a>
                                        <a href="javascript:void(0);" data-id="WZY1RPeROn"
                                            class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-operation"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-custom-class="tooltip-primary" aria-label="Delete Operation"
                                            data-bs-original-title="Delete Operation"> <i
                                                class="icon-base ti tabler-trash icon-md text-danger"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>



    </div>
@endsection
