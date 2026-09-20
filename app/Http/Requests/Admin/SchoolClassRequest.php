<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolClassRequest extends FormRequest
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
        $classId = $this->route('class')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('classes', 'name')->ignore($classId)],
            'numeric_value' => ['nullable', 'integer', 'min:0', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
            'subject_ids' => ['sometimes', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ];
    }
}
