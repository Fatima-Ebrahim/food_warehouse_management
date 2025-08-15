<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayNextInstallmentRequest extends FormRequest
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
            'paidAmount'=>'numeric|required|min:0.1' ,
            'order_id'=>'exists:orders,id|required'
        ];
    }
}
