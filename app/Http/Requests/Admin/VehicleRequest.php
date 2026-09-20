<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'registration_no' => ['required', 'string', 'max:40', Rule::unique('vehicles', 'registration_no')->ignore($vehicleId)],
            'model' => ['nullable', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'driver_phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
