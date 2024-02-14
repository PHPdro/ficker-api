<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'transaction_description' => 'string' | 'max:50',
            'date' => 'date',
            'transaction_value' => 'decimal:0,2' | 'min:1',
            'payment_method_id' => 'min:1' | 'max:4',
            'installments' => 'min:1'
        ];
    }
}
