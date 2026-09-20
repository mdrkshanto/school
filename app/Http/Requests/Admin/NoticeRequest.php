<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NoticeRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'category' => ['required', 'in:general,academic,urgent'],
            'audience' => ['required', 'in:all,students,teachers,parents'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:published_at'],
        ];
    }
}
