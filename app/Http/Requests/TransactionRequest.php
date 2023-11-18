<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
            'transaction_description' => ['required', 'string', 'max:50'],
            'category_id' => ['required'],
            'category_description' => ['required_if:category_id,0', 'string', 'max:50'],
            'date' => ['required', 'date'],
            'type_id' => ['required', 'min:1', 'max:2'],
            'transaction_value' => ['required', 'decimal:0,2', 'min:1'],
            'payment_method_id' => ['required_if:type_id,2', 'prohibited_if:type_id,1'],
            'installments' => ['required_if:payment_method_id,4', 'prohibited_unless:payment_method_id,4', 'min:1'],
            'card_id' => ['required_if:payment_method_id,4', 'prohibited_unless:payment_method_id,4']
        ];
    }
}
