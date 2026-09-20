<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectRequest extends FormRequest
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
        $subjectId = $this->route('subject')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:40', Rule::unique('subjects', 'code')->ignore($subjectId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
            'class_ids' => ['sometimes', 'array'],
            'class_ids.*' => ['integer', 'exists:classes,id'],
        ];
    }
}
