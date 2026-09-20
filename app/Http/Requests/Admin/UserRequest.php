<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [! $userId ? 'required' : 'sometimes', 'string', 'min:8', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['required', 'boolean'],
            'roles' => ['required', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }
}
