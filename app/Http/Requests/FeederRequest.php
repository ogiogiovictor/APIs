<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeederRequest extends FormRequest
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
            "assettype" => 'required',
            "latitude" => 'required|numeric',
            "longtitude" => 'required|numeric',
           // "naccode" => 'required',
            //"F11kvFeeder_parent" => 'required|alpha_num',
            "F11kvFeeder_Name" => 'required',
            //"Feeder_CBSerial" => 'required',
            // "F11kvFeeder_CBYearofManufacture" => 'required',
            // "F11kvFeeder_CB_Make" => 'required',
            // "F11kvFeeder_CB_country_of_Manufacture" => 'required',
            // "F11kvFeeder_Relay_Make" => 'required',
            // "F11kvFeeder_Relay_Type" => 'required',
            // "F11kvFeeder_CTRatio" => 'required|numeric',
            // "F11kvFeeder_RMUSerial" => 'required',
            // "F11kvFeeder_RMUYearofManufacture" => 'required',
            // "F11kvFeeder_RMU_Make" => 'required',
            // "F11kvFeeder_RMU_country_of_Manufacture" => 'required',
            // "F11kvFeeder_RMU_Type" => 'required',
            // "F11kvFeeder_Route_Length" => 'required',
            // "F11kvFeeder_Conductor_Size" => 'required',
            // "F11kvFeeder_Aluminium_Conductor" => 'required',
            // "F11kvFeeder_UP_Type" => 'required',
            // "F11kvFeeder_UP_Length" => 'required',
            // "F11kvFeeder_Manufacture" => 'required',
            // "F11kvFeeder_Ratedcurrent" => 'required',
            // "F11kvFeeder_Ratedvoltage" => 'required',
            // "F11kvFeeder_CB_Type" => 'required',
        ];
    }
}
