<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ResultEntryRequest extends FormRequest
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
            'exam_id' => ['required', 'exists:exams,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'rows.*.obtained_marks' => ['nullable', 'numeric', 'min:0'],
            'rows.*.is_absent' => ['required', 'boolean'],
        ];
    }
}
