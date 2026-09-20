<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ExamScheduleRequest extends FormRequest
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
            'subject_id' => ['required', 'exists:subjects,id'],
            'date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'full_marks' => ['required', 'numeric', 'min:1', 'max:1000'],
            'pass_marks' => ['required', 'numeric', 'min:0', 'lte:full_marks'],
        ];
    }
}
