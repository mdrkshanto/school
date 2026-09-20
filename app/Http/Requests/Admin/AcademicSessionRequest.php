<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcademicSessionRequest extends FormRequest
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
        $sessionId = $this->route('session')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('academic_sessions', 'name')->ignore($sessionId)],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
