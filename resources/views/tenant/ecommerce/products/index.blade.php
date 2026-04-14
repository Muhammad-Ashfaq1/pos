@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">E-commerce /</span> Products
    </h4>

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="mb-0 position-relative flex-grow-1 w-100">
                    <input type="text" class="form-control pe-5" placeholder="Search products...">
                    <i class="ti tabler-search position-absolute text-muted" style="top: 50%; right: 1rem; transform: translateY(-50%);"></i>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary text-nowrap" data-bs-target="#addProductModal" data-bs-toggle="modal">
                        <i class="ti tabler-plus me-1"></i> Add Product
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
