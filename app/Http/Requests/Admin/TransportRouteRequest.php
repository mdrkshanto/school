<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransportRouteRequest extends FormRequest
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
        $routeId = $this->route('route')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('transport_routes', 'name')->ignore($routeId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
            'vehicle_ids' => ['sometimes', 'array'],
            'vehicle_ids.*' => ['integer', 'exists:vehicles,id'],
        ];
    }
}
