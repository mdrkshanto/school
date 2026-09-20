<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TeacherAttendanceRequest extends FormRequest
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
            'records' => ['required', 'array', 'min:1'],
            'records.*.teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'records.*.status' => ['required', 'in:present,absent,late,leave'],
            'records.*.remark' => ['nullable', 'string', 'max:255'],
        ];
    }
}
