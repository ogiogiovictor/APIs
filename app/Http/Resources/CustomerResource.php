<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\StringHelper;


class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [ 
            'CustomerSK' => $this->CustomerSK ?? '',
            'SetupDate' => $this->SetupDate,
            'AccountNo' => $this->AccountNo,
            'BookNo' => $this->BookNo,
            'MeterNo' => $this->MeterNo,
            'OldAccountNo' => $this->OldAccountNo,
            'Surname' => $this->Surname, //We will come back to this
            'AccountType' => $this->AccountType,
            'FAccountNo' => StringHelper::removeSpecialCharsAndSlashes($this->AccountNo),
            'FirstName' => $this->FirstName,
            'OtherNames' => $this->OtherNames,
            'OldTariffCode' => $this->OldTariffCode,
            'Address' => $this->Address,
            'TarriffCode' => $this->TarriffCode,
            'State' => $this->State,
            'Mobile' => $this->Mobile,
            'Email' => $this->Email,
            'ArrearsBalance' => $this->ArrearsBalance,
            'BUID' => $this->BUID,
            'BusinessHub' =>  $this->BusinessHub,
            'Region' => $this->Region,
            'StatusCode' => $this->getStatusLabel($this->StatusCode),
            'service_center' => $this->service_center,
            'CustomerSK' => $this->CustomerSK,
            "AcctTypeDesc" => $this->AcctTypeDesc,
            "City" => $this->City,
            "DistributionID" => $this->DistributionID,
            "ConnectionType" => $this->ConnectionType,
    ];
    //return parent::toArray($request);
    }

    public function getStatusLabel($status)
    {
        $statuses = [
            'A' => 'Active',
            'I' => 'Inactive',
            'S' => 'Suspended',
            'C' => 'Closed',
            '0' => 'Inactive',
            '1' => 'Active',
        ];
    
        return $statuses[$status] ?? $status;
    }


}
