<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
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
        $bookId = $this->route('book')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:30', Rule::unique('books', 'isbn')->ignore($bookId)],
            'publisher' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:2000'],
            'quantity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
