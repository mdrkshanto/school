<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
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
            'date' => ['required', 'date', 'before_or_equal:today'],
            'session_id' => ['required', 'exists:academic_sessions,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'records.*.status' => ['required', 'in:present,absent,late,leave'],
            'records.*.remark' => ['nullable', 'string', 'max:255'],
        ];
    }
}
