<?php

namespace App\Http\Requests\Admin;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
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
        $studentId = $this->route('student')?->id;
        $ignoreUser = $studentId ? Student::find($studentId)?->user_id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'admission_no' => ['required', 'string', 'max:40', Rule::unique('students', 'admission_no')->ignore($studentId)],
            'gender' => ['required', 'in:male,female,other'],
            'dob' => ['nullable', 'date', 'before:today'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreUser)],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'session_id' => ['nullable', 'exists:academic_sessions,id'],
            'status' => ['required', 'in:active,inactive,alumni'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp,avif'],
        ];
    }
}
