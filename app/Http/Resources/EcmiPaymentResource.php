<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Test\DimensionCustomer;

class EcmiPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'TransactionDateTime' => $this->TransactionDateTime ?? 0,
            'BUID' => $this->BUID ?? '',
            'TransactionNo' => $this->TransactionNo ?? '',
            'Token' => $this->Token ?? '',
            'AccountNo' => $this->AccountNo,
            'CustomerName' => trim(DimensionCustomer::where('AccountNo', $this->AccountNo)->value('Surname')). ' ' . 
             trim(DimensionCustomer::where('AccountNo', $this->AccountNo)->value('OtherNames')),
            'MeterNo' => $this->MeterNo,
            'Amount' => $this->Amount,
            'Units' => $this->Units,
            'CostOfUnits' => $this->CostOfUnits,
            'FC' => $this->FC,
            'MMF' => $this->MMF,
            'KVA' => $this->KVA,
            'VAT' => $this->VAT,
            'TokenType' => $this->TokenType,
            'Reasons' => $this->Reasons,
            'TransactionComplete' => $this->TransactionComplete,
            'status' => $this->status,
            'transref' => $this->transref,
            'TransactionType' => $this->TransactionType,
        ];
       // return parent::toArray($request);
    }
}
