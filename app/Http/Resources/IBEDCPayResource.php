<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use App\Models\ECMIPayment;
use App\Models\EMSPayment;
use App\Helpers\StringHelper;
use App\Models\SubAccount;

use App\Models\ZonePayment;
use App\Models\ECMITransactions;

class IBEDCPayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'SetupDate' =>  isset($this->activated)  ? $this->OpenDate : $this->SetUpDate,
            'AccountNo' => $this->AccountNo,
            'FAccountNo' => StringHelper::removeSpecialCharsAndSlashes($this->AccountNo),
            'BookNo' => $this->BookNo ?? $this->booknumber,
            'MeterNo' => $this->MeterNo,
            'OldAccountNo' => $this->OldAccountNo ?? $this->oldaccountnumber,
            'Surname' => $this->Surname, //We will come back to this
            'AccountType' => isset($this->activated)  ? "Prepaid" : "Postpaid",
            'FirstName' => $this->FirstName,
            'OtherNames' => $this->OtherNames,
           // 'OldTariffCode' => $this->OldTariffCode,
            'Address' => $this->Address1 ?? $this->Address,
           // 'TarriffCode' => $this->TarriffCode,
            'State' => $this->State,
            'Mobile' => $this->Mobile,
            'Email' => $this->EMail ?? $this->email,
            'ArrearsBalance' => "", //$this->ArrearsBalance,
            'BUID' => $this->BUID,
            //'BusinessHub' =>  $this->BusinessHub,
           // 'Region' => $this->Region,
           // 'StatusCode' => $this->StatusCode,
           // 'service_center' => $this->service_center,
            //'CustomerSK' => $this->CustomerID,
           // "AcctTypeDesc" => $this->AcctTypeDesc,
           // "City" => $this->City,
            "DistributionID" => $this->DistributionID,
            //"ADC"=> $this->ADC,
           // "ConnectionType" => $this->ConnectionType,
           // "lasttransactiondate" => isset($this->activated) ? 
           // ECMIPayment::select('TransactionDateTime')->latest('TransactionDateTime')->where("AccountNo", $this->AccountNo)->value('TransactionDateTime') :
           // EMSPayment::select('PayDate')->latest('PayDate')->where("AccountNo", $this->AccountNo)->value('PayDate'),
           // "lasttoken" =>isset($this->activated) ?  ECMIPayment::select('Token')->latest('TransactionDateTime')->where("AccountNo", $this->AccountNo)->value('Token') : null,
           // "units" => isset($this->activated) ?  ECMIPayment::select('Units')->latest('TransactionDateTime')->where("AccountNo", $this->AccountNo)->value('Units') : null,
           // "amounts" => isset($this->activated) ?  ECMIPayment::select('Amount')->latest('TransactionDateTime')->where("AccountNo", $this->AccountNo)->value('Amount') : null,
           // "rate" => isset($this->activated) ?  ECMIPayment::select('CostOfUnits')->latest('TransactionDateTime')->where("AccountNo", $this->AccountNo)->value('CostOfUnits') : null,
           // "vat" => $isset($this->activated) ?  ECMIPayment::select('VAT')->latest('TransactionDateTime')->where("AccountNo", $this->AccountNo)->value('VAT') : null,
           // "lastPayment" => $this->AccountType == 'Postpaid' ?  EMSPayment::select('Payments')->latest('PayDate')->where("AccountNo", $this->AccountNo)->value('Payments') : null,
           // "lastPayDate" => $this->AccountType == 'Postpaid' ?  EMSPayment::select('PayDate')->latest('PayDate')->where("AccountNo", $this->AccountNo)->value('PayDate') : null,
           "outbalance" => isset($this->activated)  ? $this->outBalance($this->AccountNo) : null,
        ];
    }


    public function outBalance($actNo){
       // if($accType == 'Prepaid'){
            //$accountNo = Customer::where("MeterNo", $actNo)->value("AccountNo");
            $subAccountBal = SubAccount::select("SubAccountNo", "AccountNo", "AmountAttached", "Balance", "SubAccountAbbre", "ModeOfPayment", "PaymentAmount", "lastmodified")
            ->where(["AccountNo" => $actNo, "SubAccountAbbre" => 'OUTBAL'])->first();

            //return  $subAccountBal->Balance;
          

            $addBalance = 0;
            if($subAccountBal){
                $subAccountBalFpUnit = SubAccount::where(["AccountNo" => $actNo, "SubAccountAbbre" => 'FPUNIT'])->first()->Balance;
                $addBalance = $subAccountBal->Balance + $subAccountBalFpUnit;
                $subAccountBal->Balance = number_format($addBalance, 2, ".", "");
            }

            return $subAccountBal;
           
       // }
    }


}
