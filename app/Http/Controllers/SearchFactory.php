<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\SearchRepository;

class SearchFactory extends Controller
{
    public static function initalizeSearch($request){
        switch ($request->type) {
            case 'customers':
                return new SearchRepository($request);
            case 'asset':
                return null;
            case 'tickets':
                return '';
            default:
                throw new \InvalidArgumentException('Invalid payment type');
        }
    }
}
