<?php

namespace App\Repositories\Interface;

use App\Models\Vehicle;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\View\View;

interface VehicleRepositoryInterface
{
    public function index(): View;

    public function store(array $data, ?Vehicle $vehicle = null, ?Authenticatable $user = null): array;

    public function destroy(Vehicle $vehicle): array;

    public function getVehiclesListing(array $filters, ?Authenticatable $user = null): array;

    public function getVehicleFormData(Vehicle $vehicle, ?Authenticatable $user = null): array;
}
