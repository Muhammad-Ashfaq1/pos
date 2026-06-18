<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plate_number' => $this->plate_number,
            'registration_number' => $this->registration_number,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'color' => $this->color,
            'odometer' => $this->odometer,
            'is_default' => (bool) $this->is_default,
            'label' => trim(collect([$this->year, $this->make, $this->model])->filter()->implode(' ')),
        ];
    }
}
