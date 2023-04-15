<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssetRequest extends FormRequest
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
            "naccode" => 'required',
            "DSS_11KV_415V_parent" => 'required|alpha_num',
            "DSS_11KV_415V_Owner" => 'required',
            "DSS_11KV_415V_Name" => 'required',
            "DSS_11KV_415V_Address" => 'required',
            "DSS_11KV_415V_Rating" => 'required',
            "DSS_11KV_415V_Make" => 'required',
            "DSS_11KV_415V_Feederpillarr_Type" => 'required',
            "DSS_11KV_415V_FP_Condition" => 'required',
            "DSS_11KV_415V_FP_Catridge" => 'required|numeric',
            "DSS_11KV_415V_HV_Fuse" => 'required',
            "DSS_11KV_415V_HV_Fus_Condition" => 'required',
            "DSS_11KV_415V_Lightning_Arrester" => 'required',
            "DSS_11KV_415V_Serial_No" => 'required',
            "DSS_11KV_415V_Voltage_Ratio" => 'required',
            "DSS_11KV_415V_Oil_Temp" => 'required',
            "DSS_11KV_415V_Winding_Temp" => 'required',
            "DSS_11KV_415V_Manufacture_Year" => 'required',
            "DSS_11KV_415V_Installation_Year" => 'required',
            "DSS_11KV_415V_country_of_Manufacture" => 'required',
            "DSS_11KV_415V_Impedence" => 'required',
            "DSS_11KV_415V_Upriser_Number" => 'required',
            "DSS_11KV_415V_Security" => 'required',
            "DSS_11KV_415V_substation_gravelled" => 'required',
            "DSS_11KV_415V_substation_vegetation" => 'required',
            "DSS_11KV_415V_Low_Voltage_Cable_Size" => 'required'
        ];
    }
}
