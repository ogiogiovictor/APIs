<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;

class CustomerCRMDResource extends JsonResource
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
            'AccountNo' => $this->AccountNo,
            'MeterNo' => $this->MeterNo,
            'AcountType' => $this->AcountType,
            'Old_FullName' => $this->Old_FullName,
            'New_FullName' => $this->New_FullName,
            'Address' => $this->Address,
            'DistributionID' => $this->DistributionID,
            'hub' => $this->hub,
            'region' => $this->region,
            'service_center' => $this->service_center,
            'userid' => User::where("id", $this->userid)->value("name"),
            'new_surname' => $this->new_surname,
            'new_address' => $this->new_address,
            'mobile' => $this->mobile,
            'approval_type' => $this->getStatusLabel($this->approval_type),
            'confirmed_by' => $this->confirmed_by,
            'approved_by' => $this->approved_by,
            'sync' => $this->sync,
            'new_mobile' => $this->new_mobile,
            'new_firstname' => $this->new_firstname,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
        //return parent::toArray($request);
    }


    public function getStatusLabel($status)
    {
        $statuses = [
            '0' => 'Pending', // Created by CRO
            '1' => 'Reviewed By TL', // TL has approved
            '2' => 'Approved By BHM', //Aprroved by BHM
            '3' => 'Approved By Audit', //Approvd by Audit
            '4' => 'Completed', // Completed by Billing
            '5' => 'Rejected', // Rejected
        ];
    
        return $statuses[$status] ?? $status;
    }


}
