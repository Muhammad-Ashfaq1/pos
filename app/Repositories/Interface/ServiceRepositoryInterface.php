<?php

namespace App\Repositories\Interface;

use App\Models\Service;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\View\View;

interface ServiceRepositoryInterface
{
    public function index(): View;

    public function store(array $data, ?Service $service = null, ?Authenticatable $user = null): array;

    public function destroy(Service $service): array;

    public function getServicesListing(array $filters, ?Authenticatable $user = null): array;

    public function getServiceFormData(Service $service, ?Authenticatable $user = null): array;
}
