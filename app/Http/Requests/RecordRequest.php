<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordRequest extends FormRequest
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
            'ticketid' => 'required',
            'accountNo' => 'required|string|max:16',
            'address' => 'required|string',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'new_accountNo' => 'string|max:16',
            'new_firstname' => 'string',
            'new_lastname'  => 'string',
            'new_address' => 'string',
        ];
    }
}
