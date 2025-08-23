<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class BatchesStatusRequest extends FormRequest
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
            'status'   => 'nullable|in:all,consumed,remaining,expired',
            'as_of'    => 'nullable|date',
            'page'     => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:500'
        ];
    }
}
