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

    <div class="card">
        <div class="card-body text-center py-5">
            <i class="ti tabler-ticket icon-lg mb-3 text-secondary" style="font-size: 3rem;"></i>
            <h4>Manage Discount Groups</h4>
            <p class="text-muted">This module will allow you to organize your discounts into logical groups for better management.</p>
        </div>
    </div>
@endsection
