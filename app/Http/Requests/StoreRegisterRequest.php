<?php

// app/Http/Requests/StoreRegisterRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:register_requests,email',
            'phone_number' => 'required',
            'commercial_certificate' => 'required|image|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }
}
