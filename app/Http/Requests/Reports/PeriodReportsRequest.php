<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class PeriodReportsRequest extends FormRequest
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
            'months' => 'nullable|integer|min:1',
            'from'   => 'nullable|date',
            'to'     => 'nullable|date|after_or_equal:from',
            'sort' => 'nullable|in:asc,desc',
            'daysBefore'=>'nullable|integer',
            'daysAfter'=>'nullable|integer' ,
            'paymentType'=>'nullable|in:cash,installment',
            'getBy'=>'nullable|in:OrdersNumber,OrdersValue ' ,
            'status' => 'nullable|in:received,not_received,all' ,
            'limit' => 'nullable|integer|min:1|max:500',
        ];
    }
}
