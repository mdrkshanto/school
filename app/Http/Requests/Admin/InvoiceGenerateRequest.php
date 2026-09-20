<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceGenerateRequest extends FormRequest
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
            'session_id' => ['required', 'exists:academic_sessions,id'],
            'fee_structure_id' => ['required', 'exists:fee_structures,id'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
