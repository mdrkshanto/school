<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GradeRuleRequest extends FormRequest
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
        $gradeId = $this->route('gradeRule')?->id;

        return [
            'name' => ['required', 'string', 'max:10', Rule::unique('grade_rules', 'name')->ignore($gradeId)],
            'grade_point' => ['required', 'numeric', 'min:0', 'max:5'],
            'min_mark' => ['required', 'numeric', 'min:0'],
            'max_mark' => ['required', 'numeric', 'gt:min_mark', 'max:1000'],
            'remark' => ['nullable', 'string', 'max:255'],
        ];
    }
}
