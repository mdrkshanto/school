<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TransportStopRequest extends FormRequest
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
        return [
            'route_id' => ['required', 'exists:transport_routes,id'],
            'name' => ['required', 'string', 'max:255'],
            'fare' => ['required', 'numeric', 'min:0'],
            'order_index' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
