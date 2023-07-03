<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Test\DimensionCustomer;
use App\Models\Test\ZoneBills;
use Illuminate\Support\Facades\DB;

class DTBusinessHubResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentYear = date('Y');
        $currentMonth = date('n');

        return [ 
            'hub_name' => $this->hub_name,
            'asset_count' => $this->asset_count,
            'customers' =>  DimensionCustomer::where('BusinessHub', $this->hub_name)->count("AccountNo"),
            'bills' => ZoneBills::where('BUName1',  $this->hub_name)->where('BillYear', $currentYear)->where('BillMonth', $currentMonth)->sum(DB::raw('CurrentChgTotal + Vat')),
        ];
        //return parent::toArray($request);
    }
}
