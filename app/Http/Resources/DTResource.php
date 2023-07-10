<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ServiceUnit;

class DTResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [ 
            'Assetid' => $this->Assetid,
            'assettype' => $this->assettype,
            'Capture_Datetime' => $this->Capture_Datetime,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'DSS_11KV_415V_Owner' => $this->DSS_11KV_415V_Owner,
            'DSS_11KV_415V_Name' => $this->DSS_11KV_415V_Name,
            'DSS_11KV_415V_Address' => $this->DSS_11KV_415V_Address,
            'DSS_11KV_415V_Rating' => $this->DSS_11KV_415V_Rating,
            'hub_name' => $this->hub_name,
            'region' => ServiceUnit::where("Name", $this->region)->value('Region'),
            'Status' => $this->Status,
            //'getCustomerCount' => $this->getCustomerCount,
        ];
        //return parent::toArray($request);
    }
}
