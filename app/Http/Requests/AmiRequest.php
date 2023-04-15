<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AmiRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'DATE' => 'required|date'
        ];
    }

    public function messages(): array {
        return [
            'DATE.required' => 'Please ensure you are sending a DATE request'
        ];
    }
}
