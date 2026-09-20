<?php

namespace App\Http\Requests\Admin;

use App\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
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
        $teacherId = $this->route('teacher')?->id;
        $ignoreUser = $teacherId ? Teacher::find($teacherId)?->user_id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'employee_no' => ['required', 'string', 'max:40', Rule::unique('teachers', 'employee_no')->ignore($teacherId)],
            'gender' => ['required', 'in:male,female,other'],
            'dob' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreUser)],
            'qualification' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'join_date' => ['nullable', 'date'],
            'leave_date' => ['nullable', 'date', 'after_or_equal:join_date'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
