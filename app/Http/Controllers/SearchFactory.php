<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\SearchRepository;
use App\Repositories\SearchAssetRepository;

class SearchFactory extends Controller
{
    public static function initalizeSearch($request){
        $search = new SearchRepository($request);

        switch ($request->type) {
            case 'customers':
                return new SearchRepository($request);
            case 'dt_asset':
               return new SearchAssetRepository($request);
            case 'dt_feeder':
                return '';
            case 'feeder':
                return '';
            default:
                throw new \InvalidArgumentException('Invalid payment type');
        }
    }
}
