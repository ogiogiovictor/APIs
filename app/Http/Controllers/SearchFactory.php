<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\SearchRepository;
use App\Repositories\SearchAssetRepository;
use App\Repositories\SearchPaymentRepository;

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
            case 'feeder':
                return '';
            default:
                throw new \InvalidArgumentException('Invalid payment type');
        }
    }
}
