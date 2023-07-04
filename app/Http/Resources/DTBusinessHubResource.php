<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
//use App\Models\Test\DimensionCustomer;
//use App\Models\Test\ZoneBills;
use Illuminate\Support\Facades\DB;
use App\Models\DimensionCustomer;
use App\Models\ZoneBills;

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
        $previousMonth = date('n', strtotime('-1 month'));

        return [ 
            'hub_name' => $this->hub_name,
            'asset_count' => $this->asset_count,
            'customers' =>  DimensionCustomer::where('BusinessHub', $this->hub_name)->count("AccountNo"),
           // 'bills' => ZoneBills::where('BUName1',  strtoupper($this->hub_name))->where('BillYear', $currentYear)->where('BillMonth', $previousMonth)->sum(DB::raw('CurrentChgTotal + Vat')),
            'bills' => number_format(ZoneBills::where('BUName1', strtoupper($this->hub_name))->where('BillYear', $currentYear)->where('BillMonth', $previousMonth)->sum(DB::raw('CurrentChgTotal + Vat')), 2),

        ];
        //return parent::toArray($request);
    }
}
