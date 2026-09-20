<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'gt:0'],
            'method' => ['required', 'in:cash,bank,card,online,cheque'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
            'paid_at' => ['nullable', 'date'],
        ];
    }
}
