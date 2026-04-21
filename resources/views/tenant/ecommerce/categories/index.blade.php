@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Categories</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Ecommerce</li>
                    <li class="breadcrumb-item active" aria-current="page">Categories</li>
                </ol>
            </nav>
        </div>

        @can('create', \App\Models\Category::class)
            <button
                type="button"
                class="btn btn-primary"
                id="addCategoryBtn"
                data-bs-toggle="modal"
                data-bs-target="#categoryModal"
            >
                <i class="ti tabler-plus me-1"></i>
                Add Category
            </button>
        @endcan
    </div>

    <div id="categoryAlerts" class="mb-3">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form id="categoryFilterForm" method="GET" action="{{ route('tenant.ecommerce.categories.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <label for="search" class="form-label">Search</label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            class="form-control"
                            value="{{ $filters['search'] }}"
                            placeholder="Search by name or code"
                        >
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select filter-control">
                            <option value="">All</option>
                            <option value="1" @selected($filters['status'] === '1')>Active</option>
                            <option value="0" @selected($filters['status'] === '0')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="sort" class="form-label">Sort By</label>
                        <select id="sort" name="sort" class="form-select filter-control">
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="per_page" class="form-label">Per Page</label>
                        <select id="per_page" name="per_page" class="form-select filter-control">
                            @foreach ([15, 25, 50, 100] as $perPage)
                                <option value="{{ $perPage }}" @selected($filters['per_page'] === $perPage)>{{ $perPage }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-12 d-grid">
                        <button type="submit" class="btn btn-label-primary">Go</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table border-top mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($categories->count())
                        @foreach ($categories as $category)
                            <tr id="category-row-{{ $category->id }}">
                                <td>{{ $categories->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold">{{ $category->name }}</td>
                                <td>{{ $category->code ?: '—' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($category->description ?: '—', 70) }}</td>
                                <td>{{ $category->sort_order }}</td>
                                <td>
                                    <div class="d-flex flex-column gap-2">
                                        <span class="badge {{ $category->is_active ? 'bg-label-success' : 'bg-label-secondary' }}" data-status-badge>
                                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        @can('update', $category)
                                            <div class="form-check form-switch mb-0">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input category-status-toggle"
                                                    {{ $category->is_active ? 'checked' : '' }}
                                                    data-url="{{ route('tenant.ecommerce.categories.toggle-status', $category) }}"
                                                    aria-label="Toggle category status"
                                                >
                                            </div>
                                        @endcan
                                    </div>
                                </td>
                                <td class="text-nowrap">{{ $category->created_at?->format('d M Y') }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        @can('update', $category)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-icon btn-text-secondary edit-category-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#categoryModal"
                                                data-id="{{ $category->id }}"
                                                data-name="{{ $category->name }}"
                                                data-code="{{ $category->code }}"
                                                data-description="{{ $category->description }}"
                                                data-sort-order="{{ $category->sort_order }}"
                                                data-is-active="{{ $category->is_active ? 1 : 0 }}"
                                                title="Edit"
                                            >
                                                <i class="ti tabler-edit"></i>
                                            </button>
                                        @endcan

                                        @can('delete', $category)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-icon btn-text-danger category-delete-btn"
                                                data-url="{{ route('tenant.ecommerce.categories.destroy', $category) }}"
                                                data-name="{{ $category->name }}"
                                                title="Delete"
                                            >
                                                <i class="ti tabler-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No categories found</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if ($categories->count())
            <div class="card-body border-top d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="text-muted">
                    Showing {{ $categories->firstItem() }} to {{ $categories->lastItem() }} of {{ $categories->total() }} categories.
                </div>
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="categoryForm" action="{{ route('tenant.ecommerce.categories.save') }}" method="POST" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="category_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalLabel">Add Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" maxlength="150">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="code" class="form-label">Code</label>
                                <input type="text" class="form-control" id="code" name="code" maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" maxlength="1000"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" min="0" class="form-control" id="sort_order" name="sort_order" value="0">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-block">Status</label>
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="categorySubmitBtn" data-create-text="Save Category" data-update-text="Update Category">
                            Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/jquery.validate.min.js"></script>
    <script src="{{ asset('assets/js/tenant-categories.js') }}"></script>
@endsection
