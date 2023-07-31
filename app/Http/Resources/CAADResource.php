<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CAADResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [ 
            'id' => $this->id,
            'accountNo' => $this->accountNo,
            'phoneNo' => $this->phoneNo,
            'surname' => $this->surname,
            'region' => $this->region,
            'business_hub' => $this->business_hub,
            'transtype' => $this->transtype,
            'accountType' => $this->accountType,
            'transaction_type' => $this->transaction_type,
            'effective_date' => $this->effective_date,
            'amount' => $this->amount,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => $this->status
        ];
        //return parent::toArray($request);
    }
}
