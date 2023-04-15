<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IBEDCENGINE\AmiController;
use App\Http\Controllers\Authenticate\LoginController;
use App\Http\Controllers\Customer\CustomerInformation;
use App\Http\Controllers\ACE\AssetController;
use App\Http\Controllers\Service\ServiceController;
use App\Http\Controllers\ACE\FeederController;
use App\Http\Controllers\CRM\TicketController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    // return $request->user();
// });


Route::post('auth_login', [LoginController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function() {
    //Route::group(['prefix' => 'v1', 'namespace' => 'Api\v1', 'middleware' => 'OAuth'], function () {
    Route::group(['middleware' => 'OAuth'], function () {
   /*|--------------------------------------------------------------------------
    | AMI API INTEGRATION
    |-------------------------------------------------------------------------- */
        Route::apiResource('meter_consumption', AmiController::class)->only([ 'store' ]);
        Route::post('load_summary', [AmiController::class, 'loadSummary'])->name('loadSummary');
    /*|--------------------------------------------------------------------------
    | CUSTOMER API INTEGRATION - LIFAN
    |-------------------------------------------------------------------------- */
    Route::apiResource('get_customers', CustomerInformation::class)->only(['index', 'store'])->middleware(['throttle:10,1']);

     /*|--------------------------------------------------------------------------
    | ASSET-GIS API INTEGRATION FOR CMS
    |-------------------------------------------------------------------------- */
    Route::apiResource('assets', AssetController::class);
    Route::get('get_type', [ServiceController::class, 'getType'])->name('get_type');
    Route::get('get_service_type', [ServiceController::class, 'index'])->name('get_service_type');

    Route::get('get_dashboard_stats', [AssetController::class, 'stats']);

    });
});



/////////////////////////////////////// API FOR CUSTOMER MANAGEMEMENT SYSTEM ///////////////////////////////////  'middleware' => 'oAuth'
Route::group(['prefix' => 'v1', 'namespace' => 'Api\v1', 'middleware' => 'OAuth'], function () {

    Route::middleware(['auth:sanctum'])->group(function() {


        Route::get('grap_customers/{type?}', [CustomerInformation::class, 'allCustomers']);  // Get Customers

        Route::get('grap_asset/{type?}', [AssetController::class, 'getAssetWH']);  // Get Asset Warehouse

        Route::get('grap_feeder/{type?}', [FeederController::class, 'index']); //Get Feeder Warehouse
            
        Route::get('tickets', [TicketController::class, 'index']);
      
    });
});