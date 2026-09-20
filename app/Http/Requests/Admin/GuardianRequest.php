<?php

namespace App\Http\Requests\Admin;

use App\Models\Guardian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardianRequest extends FormRequest
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
        $guardianId = $this->route('guardian')?->id;
        $ignoreUser = $guardianId ? Guardian::find($guardianId)?->user_id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreUser)],
            'phone' => ['nullable', 'string', 'max:30'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
            'student_ids' => ['sometimes', 'array'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ];
    }
}
