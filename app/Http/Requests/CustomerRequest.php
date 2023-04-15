<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
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
        $input = $this->all();
        $input['AccountNo'] = trim($input['AccountNo']);
        $this->replace($input);

        return [
            "AccountNo" => 'required'
        ];
    }

    public function messages(): array {
        return [
            'AccountNo.required' => 'Provide Account/Meter No'
        ];
    }
}
