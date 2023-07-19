<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CaadRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            "accountNo" => 'required',
            "phoneNo" => 'required',
            "surname" => 'required',
            "service_center" => 'required',
            "transtype" => 'required',
            "accountType" => 'required',
            "transaction_type" => 'required',
            "effective_date" => 'required',
            "amount" => 'required',
            "remarks" => 'required',
        ];
    }
}
