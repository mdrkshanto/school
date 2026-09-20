<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SectionRequest extends FormRequest
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
        $section = $this->route('section');

        return [
            'class_id' => ['required', 'exists:classes,id'],
            'name' => ['required', 'string', 'max:60', Rule::unique('sections', 'name')->ignore($section?->id)->where('class_id', $this->input('class_id'))],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
