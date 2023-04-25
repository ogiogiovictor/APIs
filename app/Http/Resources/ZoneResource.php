<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use App\Models\BusinessUnit;
use App\Models\ECMIPayment;
use App\Models\EMSPayment;
use App\Helpers\StringHelper;

class ZoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'BookNo' => $this->booknumber,
            'AccountNo' => $this->AccountNo,
            'FAccountNo' => StringHelper::removeSpecialCharsAndSlashes($this->AccountNo),
            'MeterNo' => $this->MeterNo,
            'SetupDate' => $this->ApplicationDate,
            'TariffID' => $this->TariffID,
            'TarriffCode' => $this->TariffID, //We will come back to this
            //'OldTariffCode' => $this->OldTariffCode, //We will come back to this
            'AccountType' => 'Postpaid',
            'OpenDate' => $this->NewsetupDate,
            'Surname' => $this->Surname,
            'FirstName' => $this->FirstName,
            'CustomerName' => $this->Surname . ' ' . $this->FirstName,
            'Address' => $this->Address1 . ' ' . $this->Address2 ,
            'State' => $this->State,
            'Mobile' => $this->Mobile,
            'EMail' => $this->email,
            'ArrearsBalance' => $this->ArrearsBalance,
            'BUID' => BusinessUnit::where("BUID", $this->BUID)->value('Name'),
            'BusinessHub' => BusinessUnit::where("BUID", $this->BUID)->value('Name'),
            //'UTID' => $this->UTID,
           // 'TransID' => $this->TransID,
            'OldAccountNo' => $this->oldaccountnumber,
            'ServiceCenter' => '',
            "AcctTypeDesc" => $this->AcctTypeDesc,
            "City" => $this->City,
            "Region"=> $this->State,
            "service_center" => null,
            "StatusCode" => $this->StatusCode,
            //"DistributionID" => $this->DistributionID,
           // "ADC" => $this->ADC, 
            "ConnectionType" => $this->ConnectionType,
            "lastPayment" => $this->AccountType == 'Postpaid' ?  EMSPayment::select('Payments')->latest('Payments')->where("AccountNo", $this->AccountNo)->value('Payments') : null,
            "lastPayDate" => $this->AccountType == 'Postpaid' ?  EMSPayment::select('PayDate')->latest('PayDate')->where("AccountNo", $this->AccountNo)->value('PayDate') : null,
            "ConnectionType" => $this->ConnectionType,
            "lasttransactiondate" => null,
            "lasttoken" =>  null,
            "units" => null,
            "amounts" => null,
            "rate" => null,
            "vat" => null,
        ];
       // return parent::toArray($request);
    }
}
