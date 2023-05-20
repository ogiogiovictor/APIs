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
use App\Http\Controllers\Test\TestController;
use App\Http\Controllers\Authenticate\UserController;
use App\Http\Controllers\Authenticate\RoleController;
use App\Http\Controllers\Authenticate\PermissionController;
use App\Http\Controllers\Customer\CustomerOveriewController;
use App\Http\Controllers\Bills\CustomerBills;
use App\Http\Controllers\IBEDCENGINE\PaymentController;
use App\Http\Controllers\ACE\InjectionSubStationController;
use App\Http\Controllers\OPS\Disconnection;
use App\Http\Controllers\Search\SearchController;
use App\Http\Controllers\Authenticate\SocialController;

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


Route::post('auth_login', [LoginController::class, 'login']); // normal login
Route::post('authenticate_with_ad', [SocialController::class, 'authenticate']); // with on-prem

Route::get('social-auth/{provider}/callback', [SocialController::class, 'providerCallback']); // with azure or any other social network
Route::get('social-auth/{provider}', [SocialController::class, 'redirectoToProvider'])->name('social-redirect');


Route::middleware(['auth:sanctum'])->group(function() {
    //Route::group(['prefix' => 'v1', 'namespace' => 'Api\v1', 'middleware' => 'OAuth'], function () {
    Route::group(['middleware' => 'OAuth'], function () {
   /*|--------------------------------------------------------------------------
    | AMI API INTEGRATION
    |-------------------------------------------------------------------------- */
        Route::apiResource('meter_consumption', AmiController::class)->only([ 'store' ]);
        Route::post('load_summary', [AmiController::class, 'loadSummary'])->name('loadSummary');
        Route::get('get_summary', [AmiController::class, 'getSummary'])->name('getSummary');  // Not implemented Yet in React
        Route::get('get_all_connection', [AmiController::class, 'getAll'])->name('getAll');  // Not implemented Yet in React
        Route::get('event_up_down', [AmiController::class, 'eventUpDown'])->name('eventUpDown');  // Not implemented Yet in React
    /*|--------------------------------------------------------------------------
    | CUSTOMER API INTEGRATION - LIFAN
    |-------------------------------------------------------------------------- */
    Route::apiResource('get_customers', CustomerInformation::class)->only(['index', 'store'])->middleware(['throttle:10,6']);

     /*|--------------------------------------------------------------------------
    | ASSET-GIS API INTEGRATION FOR CMS
    |-------------------------------------------------------------------------- */
    Route::apiResource('assets', AssetController::class);
    Route::get('get_type', [ServiceController::class, 'getType'])->name('get_type');
    Route::get('get_service_type', [ServiceController::class, 'index'])->name('get_service_type');

    });
});



/////////////////////////////////////// API FOR CUSTOMER MANAGEMEMENT SYSTEM ///////////////////////////////////  'middleware' => 'oAuth'
Route::group(['prefix' => 'v1', 'namespace' => 'Api\v1', 'middleware' => 'OAuth'], function () {

    Route::middleware(['auth:sanctum'])->group(function() {

        Route::get('get_user', [UserController::class, 'getUser']);

        Route::get('get_dashboard_stats', [AssetController::class, 'stats']);

        Route::get('grap_customers/{type?}/{status?}', [CustomerInformation::class, 'allCustomers']);  // Get Customers
            
        Route::get('tickets', [TicketController::class, 'index']);

        Route::post('tickets', [TicketController::class, 'show']);

        // Only for Administrative Users
      /*  Route::prefix('roles')->middleware('role:admin')->group(function() {
         
            Route::apiResource('roles', RoleController::class);
            Route::apiResource('permissions', PermissionController::class);

        });
        */

         //Customer 360
         Route::get('customer360/{account?}/{dss?}', [CustomerOveriewController::class, 'customer360']);

        // Route::apiResource('get_customers', CustomerInformation::class)->only(['index', 'store'])->middleware(['throttle:10,1']);

         //Get Bill
         Route::get('getbills', [CustomerBills::class, 'getBills']);
         Route::get('grap_asset/{type?}', [AssetController::class, 'getAssetWH']);  // Get Asset Warehouse

         //Create CRMD Customer Record
         Route::post('crmd', [CustomerInformation::class, 'cstore']); // Not Yet Implemented
         Route::get('grap_feeder/{type?}', [FeederController::class, 'index']); //Get Feeder Warehouse
         Route::get('/payments', [PaymentController::class, 'getPayments']);
         Route::get('/paymentDetails/{FAccount?}/{Token?}/{type?}', [PaymentController::class, 'getPaymentDetails']);
         Route::get('transmission_stations', [InjectionSubStationController::class, 'getTransmissionStations']);
        
         Route::get('all_disconnections', [Disconnection::class, 'index']);

         Route::get('billDetails/{billID?}', [CustomerBills::class, 'getBillDetails']); 
         Route::get('get_crmd/pending', [CustomerInformation::class, 'getCrmd']);

         Route::post('updatecrmdstate', [CustomerInformation::class, 'updateStatus']);

         Route::post('search_any', [SearchController::class, 'searching']);

         Route::get('get_owing_customers', [CustomerInformation::class, 'percentageOwed']);

         Route::get('get_events', [AmiController::class, 'getSummary'])->name('getEvents');  // Not implemented Yet in React
         
       
         
    });
});




/*********************************** ROUTE FOR TESTING ON LOCAL MACHINE ************************************** */

Route::post('auth_login_test', [TestController::class, 'login']);

/////////////////////////////////////// API FOR CUSTOMER MANAGEMEMENT SYSTEM ///////////////////////////////////  'middleware' => 'oAuth'
Route::group(['prefix' => 'v2', 'namespace' => 'Api\v2', 'middleware' => 'OAuth'], function () {

    Route::middleware(['auth:sanctum'])->group(function() {

        Route::get('get_user', [TestController::class, 'getUser']);

        Route::get('get_dashboard_stats', [TestController::class, 'stats']);

        //Route::get('grap_customers/{type?}', [TestController::class, 'allCustomers']);  // Get Customers

        Route::get('tickets', [TestController::class, 'tindex']);

        Route::post('tickets', [TestController::class, 'tshow']);

       // Only for Administrative Users
        Route::prefix('roles')->middleware('role:admin')->group(function() {
         
            Route::apiResource('roles', RoleController::class);
            Route::apiResource('permissions', PermissionController::class);

        });

         //Customer 360
         Route::get('customer360/{account?}/{dss?}', [TestController::class, 'customer360']);

         //Route::apiResource('get_customers', CustomerInformation::class)->only(['index', 'store'])->middleware(['throttle:10,1']);

         //Get Bill
         Route::get('getbills', [TestController::class, 'getBills']);

         //Create CRMD Customer Record
         Route::post('crmd', [TestController::class, 'cstore']);
         Route::get('grap_feeder/{type?}', [TestController::class, 'findex']); //Get Feeder Warehouse
         Route::get('/payments', [TestController::class, 'getPayments']);
         Route::get('/paymentDetails/{FAccount?}/{Token?}/{type?}', [TestController::class, 'getPaymentDetails']);
         Route::get('transmission_stations', [TestController::class, 'getTransmissionStations']);

         Route::get('billDetails/{billID?}', [TestController::class, 'getBillDetails']); 
         Route::get('get_crmd/pending', [TestController::class, 'getCrmd']);

         Route::post('add_assets', [TestController::class, 'storeAsset']); //API Resource for Asset already in v1

         Route::post('updatecrmdstate', [TestController::class, 'updateStatus']);

         Route::post('grap_feeder', [TestController::class, 'addFeeder']); 
         //

         Route::post('search_any', [TestController::class, 'searchRecords']);

         
        Route::get('grap_customers/{type?}/{status?}', [TestController::class, 'allCustomers']);  // testing
            
        Route::get('grap_asset/{type?}', [TestController::class, 'getAssetWH']);  // Get Asset Warehouse

        Route::get('grap_customers_status/{statusCode?}/{postpaid?}', [TestController::class, 'customerByStatus']);  

      
    });
});

/*********************************** END OF ROUTE FOR TESTING ON LOCAL MACHINE ************************************** */
