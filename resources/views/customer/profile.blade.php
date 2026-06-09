@extends('layouts.customer-portal')

@section('title', 'Profile')

@section('content')
    <div class="card border-0 shadow-sm" style="max-width:560px;">
        <div class="card-header"><h6 class="mb-0 fw-bold">My Profile</h6></div>
        <div class="card-body">
            <form method="POST" action="{{ route('customer.profile.update') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $customer->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="{{ $customer->email }}" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $customer->address) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary fw-bold">Save changes</button>
            </form>
        </div>
    </div>
@endsection
