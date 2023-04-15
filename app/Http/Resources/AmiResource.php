<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AmiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "MSNO" => $this->MSNO,
            "DATE" => $this->DATE,
            "SAVEDB_TIME" => $this->SAVEDB_TIME,
            "BEGINTIME" => $this->BEGINTIME,
            "ENDTIME" => $this->ENDTIME,
            "KWH_ABS" => $this->KWH_ABS,
            "KWH_ABS_START" => $this->KWH_ABS_START,
            "KWH_ABS_END" => $this->KWH_ABS_END,
            "Region" => $this->Region,
            "BusinessHub" => $this->BusinessHub,
            "Transformer" => $this->Transformer,
            "AssetType" => $this->AssetType,
        ];
        //return parent::toArray($request);
    }
}
