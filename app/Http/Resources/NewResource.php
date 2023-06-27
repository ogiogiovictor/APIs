<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use App\Models\ECMIPayment;
use App\Models\EMSPayment;
use App\Helpers\StringHelper;


class NewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                'SetupDate' => $this->SetupDate,
                'AccountNo' => $this->AccountNo,
                'FAccountNo' => StringHelper::removeSpecialCharsAndSlashes($this->AccountNo),
                'BookNo' => $this->BookNo,
                'MeterNo' => $this->MeterNo,
                'OldAccountNo' => $this->OldAccountNo,
                'Surname' => $this->Surname, //We will come back to this
                'AccountType' => $this->AccountType,
                'FirstName' => $this->FirstName,
                'OtherNames' => $this->OtherNames,
                'OldTariffCode' => $this->OldTariffCode,
                'Address' => $this->Address1 ?? $this->Address,
                'TarriffCode' => $this->TarriffCode,
                'State' => $this->State,
                'Mobile' => $this->Mobile,
                'Email' => $this->Email,
                'ArrearsBalance' => $this->ArrearsBalance,
                'BUID' => $this->BUID,
                'BusinessHub' =>  $this->BusinessHub,
                'Region' => $this->Region,
                'StatusCode' => $this->StatusCode,
                'service_center' => $this->service_center,
                //'CustomerSK' => $this->CustomerID,
                "AcctTypeDesc" => $this->AcctTypeDesc,
                "City" => $this->City,
                "DistributionID" => $this->DistributionID,
                //"ADC"=> $this->ADC,
                "ConnectionType" => $this->ConnectionType,
                "lasttransactiondate" => $this->AccountType == 'Prepaid' ? 
                ECMIPayment::select('TransactionDateTime')->latest('TransactionDateTime')->where("AccountNo", $this->AccountNo)->value('TransactionDateTime') :
                EMSPayment::select('PayDate')->latest('PayDate')->where("AccountNo", $this->AccountNo)->value('PayDate'),
                "lasttoken" => $this->AccountType == 'Prepaid' ?  ECMIPayment::select('Token')->latest('TransactionDateTime')->where("AccountNo", $this->AccountNo)->value('Token') : null,
                "units" => $this->AccountType == 'Prepaid' ?  ECMIPayment::select('Units')->latest('TransactionDateTime')->where("AccountNo", $this->AccountNo)->value('Units') : null,
                "amounts" => $this->AccountType == 'Prepaid' ?  ECMIPayment::select('Amount')->latest('TransactionDateTime')->where("AccountNo", $this->AccountNo)->value('Amount') : null,
                "rate" => $this->AccountType == 'Prepaid' ?  ECMIPayment::select('CostOfUnits')->latest('TransactionDateTime')->where("AccountNo", $this->AccountNo)->value('CostOfUnits') : null,
                "vat" => $this->AccountType == 'Prepaid' ?  ECMIPayment::select('VAT')->latest('TransactionDateTime')->where("AccountNo", $this->AccountNo)->value('VAT') : null,
               // "lastPayment" => $this->AccountType == 'Postpaid' ?  EMSPayment::select('Payments')->latest('PayDate')->where("AccountNo", $this->AccountNo)->value('Payments') : null,
               // "lastPayDate" => $this->AccountType == 'Postpaid' ?  EMSPayment::select('PayDate')->latest('PayDate')->where("AccountNo", $this->AccountNo)->value('PayDate') : null,

        ];
        //return parent::toArray($request);
    }
}
