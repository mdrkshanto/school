<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BookIssueRequest extends FormRequest
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
            'book_id' => ['required', 'exists:books,id'],
            'user_id' => ['required', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
