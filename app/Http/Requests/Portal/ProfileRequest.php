<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
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
        $userId = auth()->id();

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        ];
    }
}
