<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZonePaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'PaymentID' => $this->PaymentID ?? 0,
            'BillID' => $this->BillID,
            'PaymentTransactionId' => $this->PaymentTransactionId,
            'receiptnumber' => $this->receiptnumber,
            'PaymentSource' => $this->PaymentSource,
            'MeterNo' => $this->MeterNo,
            'AccountNo' => $this->AccountNo,
            'PayDate' => $this->PayDate,
            'PayMonth' => $this->PayMonth,
            'PayYear' => $this->PayYear,
            'TotalDue' => $this->TotalDue,
            'Payments' => $this->Payments,
            'Balance' => $this->Balance,
            'Processed' => $this->Processed,
            'ProcessedDate' => $this->ProcessedDate,
            'Reconciled' => $this->Reconciled,
            'ReconciledBy' => $this->ReconciledBy,
            'ReversedBy' => $this->ReversedBy,
            'DateEngtered' => $this->DateEngtered
        ];
        //return parent::toArray($request);
    }
}
