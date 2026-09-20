<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeeStructureRequest extends FormRequest
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
        $id = $this->route('feeStructure')?->id;

        return [
            'session_id' => ['required', 'exists:academic_sessions,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'name' => ['required', 'string', 'max:255', Rule::unique('fee_structures', 'name')->ignore($id)->where('session_id', $this->input('session_id'))],
            'type' => ['required', 'in:admission,monthly,one_time,transport'],
            'amount' => ['required', 'numeric', 'min:0'],
            'is_optional' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
