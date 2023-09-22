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
use App\Http\Controllers\General\GeneralController;
use App\Http\Controllers\Meters\MeterController;
use App\Http\Controllers\Export\ExportController;
use App\Http\Controllers\CAAD\CaadController;


///////////////////////////////////////////////////////////////
# ALTERNATE PAYMENT <CONTROLLERS>
//////////////////////////////////////////////////////////////
use App\Http\Controllers\AlternatePayment\AuthenticationController;
use App\Http\Controllers\AlternatePayment\GetCustomerController;
use App\Http\Controllers\AlternatePayment\PaymentProcessingController;
use App\Http\Controllers\AlternatePayment\LogoutController;
use App\Http\Controllers\FileDownloadController;
use App\Http\Models\CRMDCustomers;

///////////////////////////////////////////////////////////////
# END OF ALTERNATE PAYMENT <CONTROLLERS>
//////////////////////////////////////////////////////////////


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

        //User
        Route::get('get_user', [UserController::class, 'getUser']);
        Route::get('all_users', [UserController::class, 'getAllUsers']);
        Route::post('reg_users', [UserController::class, 'addUser']);

        //Dashboard
        Route::get('get_dashboard_stats', [AssetController::class, 'stats']);

        //Customers
        Route::get('grap_customers/{type?}/{status?}', [CustomerInformation::class, 'allCustomers']);  // Get Customers
        Route::get('customer360/{account?}/{dss?}/{AccountType?}/{MeterNo?}', [CustomerOveriewController::class, 'customer360']);
        Route::get('all_disconnections', [Disconnection::class, 'index']);
        Route::get('get_owing_customers', [CustomerInformation::class, 'percentageOwed']);

        //Tickets
        Route::get('tickets', [TicketController::class, 'index'])->middleware('before');
        Route::post('tickets', [TicketController::class, 'show']);
        
        //Create CRMD Customer Record
        Route::middleware(['before'])->group(function () {
           
           
            
        });

        Route::get('get_crmd/pending', [CustomerInformation::class, 'getCrmd']);
        Route::post('add_customer', [CustomerInformation::class, 'addNewCustomer']); 
        Route::post('updatecrmdstate', [CustomerInformation::class, 'updateStatus']);
        Route::post('crmd', [CustomerInformation::class, 'cstore']); 

        //Customer Bill
        Route::get('bills', [CustomerBills::class, 'getBills'])->middleware('before');;
        Route::get('billDetails/{billID?}', [CustomerBills::class, 'getBillDetails']); 

        //Customer Payment
        Route::get('/payments', [PaymentController::class, 'getPayments']);
        Route::get('/paymentDetails/{FAccountNo?}/{Token?}/{type?}', [PaymentController::class, 'getPaymentDetails']);
       

        //Assets
        Route::get('grap_asset/{type?}', [AssetController::class, 'getAssetWH']);  // Get Asset Warehouse
        Route::get('grap_feeder/{type?}', [FeederController::class, 'index']); //Get Feeder Warehouse
        Route::get('transmission_stations', [InjectionSubStationController::class, 'getTransmissionStations']);
        Route::post('grap_feeder', [FeederController::class, 'addFeeder']); 
        Route::post('add_assets', [AssetController::class, 'store']);

        //AMI
        Route::middleware(['before'])->group(function () {
           
            Route::get('events', [AmiController::class, 'getAll'])->name('getAll');  
        });
        Route::get('get_ami_summary', [AmiController::class, 'getSummary'])->name('getAmiSummary');
        Route::get('ami_monthly_summary', [AmiController::class, 'monthlySummary'])->name('monthlySummary');
        Route::get('all_feeders_with_myto/{type?}', [AmiController::class, 'FeederDetails'])->name('FeederDetails');

        //General
        Route::post('search_any', [SearchController::class, 'searching']);

        // Only for Administrative Users
        Route::prefix('roles')->group(function() {
         
           // Route::apiResource('roles', RoleController::class);
            Route::get('get_roles', [RoleController::class, 'getRole'])->middleware('before');;
            Route::post('assign_roles', [RoleController::class, 'assignMenuRole']);
            Route::post('permissions', [PermissionController::class, 'store']);
            Route::get('permissions', [PermissionController::class, 'index'])->middleware('before');;
            Route::post('assign_permission', [PermissionController::class, 'giveMePermission']);
            
        });


        //CAAD PROCESS FLOW --

        Route::get('caad_process_flow', [CaadController::class, 'getApproval'])->middleware('before');; //Admin Router
        Route::post('process_caad_request', [CaadController::class, 'addCAAD']);
    

        Route::get('allcaad', [CaadController::class, 'getAllCAAD'])->middleware('before');
        Route::get('caads', [CaadController::class, 'allCAAD'])->middleware('before');

        

        Route::post('upload_bulk_caad', [CaadController::class, 'BulkCAADUpload']);
        Route::get('storage/{folder}/{filename}', [FileDownloadController::class, 'show']);

        Route::post('caad_approval_request', [CaadController::class, 'CaadApprovalRequest']);
        Route::post('caad_reject_request', [CaadController::class, 'CaadRejectRequest']);
      

        Route::prefix('stsix')->group(function() {
         
            Route::post('kctgeneration', [CustomerInformation::class, 'generatekct']);
            
        });

        Route::get('get_acccess', [UserController::class, 'getAccess']);
        Route::post('add_customer', [CustomerInformation::class, 'addNewCustomerCRMD']); 
        Route::get('pending_customer_validation', [CustomerInformation::class, 'pendingCustomer']); 

        Route::post('updatenewlycreated', [CustomerInformation::class, 'updateCustomer']);
        Route::get('access_control_list', [UserController::class, 'AccessControl']); 
        Route::get('get_permission_role/{role_id}', [UserController::class, 'getRolePermission']);


        Route::get('get_dropdown_all', [GeneralController::class, 'getAllDrops']);
        Route::get('dt_billing_efficency', [AssetController::class, 'dtBillingEffiency']);

        Route::post('add_meter', [MeterController::class, 'addMeter']);
        Route::get('get_meter', [MeterController::class, 'getMeter']);
        Route::get('customer_all_region/{region}', [MeterController::class, 'getCustomerRegion']);

        Route::get('get_nsts_customers', [CustomerInformation::class, 'nonStsCusomters']);

        Route::post('export_dt', [ExportController::class, 'exportExcel']);

        //Route::apiResource('assets', AssetController::class); //Need to fix this maybe change the assets to massets

        Route::post('export_customers', [ExportController::class, 'exportExcel']);

        Route::get('dt_by_business_hub', [AssetController::class, 'DTBusinessHub']);
        Route::get('dts_in_business_hub/{hubName}', [AssetController::class, 'ListDTS']);
        Route::get('dts_in_business_hub/{hubName}/{dssID}', [AssetController::class, 'customerList']);

        Route::get('my_approval', [CustomerInformation::class, 'ApprovedCustomers']);

        Route::post('assign_user_menu', [UserController::class, 'AssignUserMenu']);

        Route::post('mlogout', [UserController::class, 'userLogout']);

        Route::get('bill_payment_summary', [CustomerInformation::class, 'PaymentBillSummary']);

        Route::post('crmd_store', [CustomerOveriewController::class, 'crmdStore']);
        Route::get('get_crmd_customers', [CustomerOveriewController::class, 'getCustomers']);
        Route::get('get_crmd_pending', [CustomerOveriewController::class, 'getPendingCustomers']);
        Route::post('process_crmd_transaction', [CustomerOveriewController::class, 'processTransaction']);
        Route::post('reject_crmd_transaction', [CustomerOveriewController::class, 'rejectTransaction']);
        
         
    });
});




/*********************************** ROUTE FOR TESTING ON LOCAL MACHINE ************************************** */

Route::post('auth_login_test', [TestController::class, 'login']);
Route::post('forgot-password', [TestController::class, 'forgotPassword']);

Route::get('reset-password/{token}',  [TestController::class, 'changePassword'])->middleware('guest')->name('password.reset');
Route::post('reset-password', [TestController::class, 'resetPassword']); 
Route::get('check-password/{token}',  [TestController::class, 'checkPassword'])->middleware('guest');

/////////////////////////////////////// API FOR CUSTOMER MANAGEMEMENT SYSTEM ///////////////////////////////////  'middleware' => 'oAuth'
Route::group(['prefix' => 'v2', 'namespace' => 'Api\v2', 'middleware' => 'OAuth'], function () {
//Route::group(['prefix' => 'v2', 'namespace' => 'Api\v2', 'middleware' => ['OAuth', 'before'] ], function () {
    

    Route::middleware(['auth:sanctum'])->group(function() {

        Route::get('get_user', [TestController::class, 'getUser']);
        Route::get('get_dashboard_stats', [TestController::class, 'stats']);

        //Route::get('grap_customers/{type?}', [TestController::class, 'allCustomers']);  // Get Customers

        Route::get('tickets', [TestController::class, 'tindex']);
        Route::post('tickets', [TestController::class, 'tshow']);

         //Customer 360
         Route::get('customer360/{account?}/{dss?}/{accountType}/{MeterNo}', [TestController::class, 'customer360']);

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
        
        Route::get('grap_customers/{type?}/{status?}', [TestController::class, 'allCustomers']);  // testing
            
        Route::get('grap_asset/{type?}', [TestController::class, 'getAssetWH']);  // Get Asset Warehouse

        Route::get('grap_customers_status/{statusCode?}/{postpaid?}', [TestController::class, 'customerByStatus']);  

        Route::post('search_any', [TestController::class, 'searchRecords']);
       

        Route::get('all_users', [TestController::class, 'getAllUsers']);
        Route::post('reg_users', [TestController::class, 'addUser']);


        // Only for Administrative Users
        //Route::prefix('roles')->middleware('role:admin')->group(function() {
        Route::prefix('roles')->group(function() {
         
        //Route::apiResource('roles', RoleController::class);
        Route::get('get_roles', [RoleController::class, 'getRole']);
        Route::post('assign_roles', [RoleController::class, 'assignMenuRole']);
        Route::post('permissions', [PermissionController::class, 'store']);
        Route::get('permissions', [PermissionController::class, 'index']);
        
        });


        Route::get('get_acccess', [TestController::class, 'getAccess']);
        Route::post('add_customer', [TestController::class, 'addNewCustomer']); 
        Route::get('pending_customer_validation', [TestController::class, 'pendingCustomer']); 

        Route::post('updatenewlycreated', [TestController::class, 'updateCustomer']);
        Route::get('access_control_list', [TestController::class, 'AccessControl']); 
        Route::get('get_permission_role/{role_id}', [TestController::class, 'getRolePermission']);
        
        Route::post('add_meter', [TestController::class, 'addMeter']);
        Route::get('get_meter', [TestController::class, 'getMeter']);
        Route::get('customer_all_region/{region}', [TestController::class, 'getCustomerRegion']);
        Route::get('get_dropdown_all', [TestController::class, 'getAllDrops']);

        Route::post('export_dt', [TestController::class, 'exportExcel']);
        Route::post('export_customers', [ExportController::class, 'exportExcel']);


        Route::get('dt_by_business_hub', [TestController::class, 'DTBusinessHub']);

        Route::get('my_approval', [TestController::class, 'ApprovedCustomers']);

        Route::post('assign_user_menu', [TestController::class, 'AssignUserMenu']);

        Route::post('mlogout', [TestController::class, 'userLogout']);

        Route::post('upload_bulk_caad', [TestController::class, 'BulkCAADUpload']);

        Route::get('allcaad', [TestController::class, 'getAllCAAD']);
        Route::get('allcaadbatch', [TestController::class, 'getAllCAADBatch']);
        Route::post('process_caad_request', [TestController::class, 'addCAAD']);
       // Route::get('storage/{folder}/{filename}', [FileDownloadController::class, 'show']);

        Route::post('caad_approval_request', [TestController::class, 'CaadApprovalRequest']);
        Route::post('caad_reject_request', [TestController::class, 'CaadRejectRequest']);


        Route::post('change_password', [TestController::class, 'changePassword']); // When i am logged in 

        Route::get('caads', [TestController::class, 'allCAAD']);

      

        
      
    });
});

/*********************************** END OF ROUTE FOR TESTING ON LOCAL MACHINE ************************************** */





/************************************* IBEDC ALTERNATE PAYMENT SYSTEM **************************************************/
Route::group(['prefix' => 'v3ibedc_AUTH_token', 'namespace' => 'Api\v3', 'middleware' => 'alternate'], function () {

    Route::prefix('authenticate')->group(function() {
        Route::post('auth_login', [AuthenticationController::class, 'login']); // normal login    


        Route::group(['middleware' => 'customer_dashboard'], function () {
           // Route::middleware(['auth:sanctum'])->group(function() {
          Route::get('get_customer_details', [GetCustomerController::class, 'getCustomerDetails']); // normal login   
          Route::get('customer360/{account?}/{dss?}/{AccountType?}/{MeterNo?}', [CustomerOveriewController::class, 'customer360']); 

          Route::post("process_payment", [PaymentProcessingController::class, 'makePayment']);
          Route::post("complete_payment", [PaymentProcessingController::class, 'processPayment']);
          Route::get("payment_source", [PaymentProcessingController::class, 'paymentSource']);
          Route::post("payment_retry", [PaymentProcessingController::class, 'retryPayment']);
          Route::post("help", [PaymentProcessingController::class, 'ContactUs']);

          Route::post('plogout', [LogoutController::class, 'userLogout']);
           // });
        });
        
    });

});




require_once __DIR__.'/custom.php';