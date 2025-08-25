<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name'=>'nullable |string ',
            'email' => 'sometimes|email|unique:users,email,' . $this->user()->id,
            // كلمة السر الحالية مطلوبة فقط إذا المستخدم حابب يغير كلمة السر
            'current_password' => 'required_with:new_password|string|min:4',

            // كلمة السر الجديدة اختيارية، لكن إذا انبعثت لازم تتأكد وتكون مؤكدة
            'new_password'     => 'nullable|string|min:8|confirmed',
            'customer.phone_number' => 'sometimes|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string'   => 'الاسم يجب أن يكون نصاً صحيحاً',
            'email.email'   => 'الرجاء إدخال بريد إلكتروني صالح',
            'email.unique'  => 'البريد الإلكتروني مستخدم بالفعل',

            // كلمة السر
            'current_password.required_with' => 'كلمة السر الحالية مطلوبة عند تغيير كلمة السر',
            'current_password.min'           => 'كلمة السر الحالية يجب ألا تقل عن 4 محارف',

            'new_password.min'        => 'كلمة السر الجديدة يجب ألا تقل عن 8 محارف',
            'new_password.confirmed'  => 'تأكيد كلمة السر الجديدة غير متطابق',

            'customer.phone_number.max' => 'رقم الهاتف لا يجب أن يتجاوز 20 محرفاً',
        ];
    }

}
