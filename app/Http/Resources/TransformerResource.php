<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\HighTension;
use App\Models\Feeders;
use App\Models\ServiceUnit;

class TransformerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        return [
            'Assetid' => $this->Assetid,
            'assettype' => $this->assettype,
            'AssetName' => $this->AssetName,
            'DSS_11KV_415V_Make' => $this->DSS_11KV_415V_Make,
            'DSS_11KV_415V_Rating' => $this->DSS_11KV_415V_Rating,
            'DSS_11KV_415V_Address' => $this->DSS_11KV_415V_Address,
            'DSS_11KV_415V_Owner' => $this->DSS_11KV_415V_Owner,
            'hub_name' => $this->hub_name,
            'Region' => ServiceUnit::where("Name", $this->DSS_11KV_415V_Owner)->first()->Region,
            'Status' => $this->Status,
            'DSS_11KV_415V_parent' => $this->DSS_11KV_415V_parent,
            'longtitude' => $this->longtitude,
            'latitude' => $this->latitude,
            'HT_AssetID' => HighTension::where("Assetid", $this->DSS_11KV_415V_parent)->first()->HT_11KV_parent,
            'FeederName' => Feeders::where("Assetid", HighTension::where("Assetid", $this->DSS_11KV_415V_parent)->first()->HT_11KV_parent)->first()->FeederName,
            'Feeder_Asset_Type' => Feeders::where("Assetid", HighTension::where("Assetid", $this->DSS_11KV_415V_parent)->first()->HT_11KV_parent)->first()->assetType,
        ];
    }
}

