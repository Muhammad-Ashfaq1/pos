<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\VehicleResource;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $vehicles = Vehicle::query()
            ->where('customer_id', $customer->getKey())
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        return VehicleResource::collection($vehicles)->response();
    }
}
