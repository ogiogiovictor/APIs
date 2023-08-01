<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\SearchRepository;
use App\Repositories\SearchAssetRepository;
use App\Repositories\SearchPaymentRepository;
use App\Repositories\SearchBillingRepository;
use App\Repositories\SearchFeederRepository;
use App\Repositories\SearchTicketRepository;
use App\Repositories\AmiRepository;
use App\Repositories\CaadRepository;

class SearchFactory extends Controller
{
    public static function initalizeSearch($request){
        $search = new SearchRepository($request);

        switch ($request->type) {
            case 'customers':
                return new SearchRepository($request);
            case 'dt_asset':
               return new SearchAssetRepository($request);
            case 'search_payment':
                return new SearchPaymentRepository($request);
            case 'search_bills':
                return new SearchBillingRepository($request);
            case 'search_feeder':
                return new SearchFeederRepository($request);
            case 'tickets':
                return new SearchTicketRepository($request);
            case 'search_events_summary':
                return new AmiRepository($request);
            case 'mycaads':
                return new CaadRepository($request);
            
            default:
                throw new \InvalidArgumentException('Invalid payment type');  
        }
    }
}
