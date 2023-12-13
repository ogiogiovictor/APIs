<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Middleware\PrepareSetup;
use Illuminate\Support\Facades\DB;


/************************************* IBEDC ALTERNATE PAYMENT SYSTEM **************************************************/
Route::group(['prefix' => 'enter_AWMXS4dnsY', 'namespace' => 'Api\v5'], function () {

Route::get('get_middleware_transactions', [PrepareSetup::class, 'getTransaction'])->name('get_middleware_transactions');  


//
Route::get('get_trigger_ecmi', [PrepareSetup::class, 'getEcmiTrigger'])->name('get_trigger_ecmi');  
Route::get('get_whois_ecmi', [PrepareSetup::class, 'getWhoisTrigger'])->name('get_whois_ecmi');  


//https://apiengine.ibedc.com:7443/api/enter_AWMXS4dnsY/make_repose
//Route::post('make_repose', [PrepareSetup::class, 'makeitsmart'])->name('make_repose');  
Route::post('make_repose_update', [PrepareSetup::class, 'prepareIntegration'])->name('make_repose_update'); 
Route::post('get_engine', [PrepareSetup::class, 'getEngine'])->name('get_engine');
Route::post('run_street_engine', [PrepareSetup::class, 'runStreet'])->name('run_street_engine');  

});

