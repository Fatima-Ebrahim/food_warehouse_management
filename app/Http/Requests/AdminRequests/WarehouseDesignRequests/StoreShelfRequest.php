<?php
namespace App\Http\Requests\AdminRequests\WarehouseDesignRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShelfRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => 'nullable|string|max:255|unique:shelves,code',
            'cabinet_id' => 'nullable|integer|exists:cabinets,id',
            'height' => 'required|numeric|min:0',
            'current_weight' => 'required|numeric|min:0',
            'max_weight' => 'required|numeric|min:0',
            'current_length' => 'required|numeric|min:0',
            'max_length' => 'required|numeric|min:0',
            'levels' => 'required|integer|min:1',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'code.unique' => 'This code is already in use. Please choose a different one.',
            'height.required' => 'Height is required.',
            'height.numeric' => 'Height must be a number.',
            'height.min' => 'Height must be greater than or equal to 0.',
            'current_weight.required' => 'Current weight is required.',
            'current_weight.numeric' => 'Current weight must be a number.',
            'current_weight.min' => 'Current weight must be greater than or equal to 0.',
            'max_weight.required' => 'Max weight is required.',
            'max_weight.numeric' => 'Max weight must be a number.',
            'max_weight.min' => 'Max weight must be greater than or equal to 0.',
            'current_length.required' => 'Current length is required.',
            'current_length.numeric' => 'Current length must be a number.',
            'current_length.min' => 'Current length must be greater than or equal to 0.',
            'max_length.required' => 'Max length is required.',
            'max_length.numeric' => 'Max length must be a number.',
            'max_length.min' => 'Max length must be greater than or equal to 0.',
            'levels.required' => 'Levels is required.',
            'levels.integer' => 'Levels must be an integer.',
            'levels.min' => 'Levels must be greater than or equal to 1.',
            'cabinet_id.exists' => 'The provided cabinet ID does not exist.',
        ];
    }
}
