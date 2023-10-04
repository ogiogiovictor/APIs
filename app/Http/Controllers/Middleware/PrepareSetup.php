<?php

namespace App\Http\Controllers\Middleware;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Middleware\Transactions;
use App\Models\Middleware\Resplog;
use App\Models\Middleware\Ecmsdb;
use App\Models\Middleware\Ewhois;
use App\Models\Middleware\MakeSave;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Helpers\StringHelper;
use App\Models\ECMITransactions;
use App\Models\ECMIPaymentTransaction;
use App\Models\ECMITokenLogs;
use App\Models\ECMIHoldMode;
use App\Models\SubAccountPayment;
use Illuminate\Support\Str;
use App\Models\ZoneECMI;
use Illuminate\Support\Facades\Validator;
use App\Models\Middleware\AuditSubAccountPayment;
use App\Models\Middleware\AuditTokenLog;
use App\Models\Middleware\AuditTransaction;
use App\Models\Middleware\AuditNewTransactions;
use App\Models\Middleware\AuditPaymentTransaction;
use App\Models\Middleware\LogDeletedTransactions;
use App\Models\Middleware\LogSubAccountPayments;
use App\Models\Middleware\LogInsertSubAccountDeductions;


class PrepareSetup extends BaseApiController
{
    

    public function getTransaction(){
        $getTransaction = Transactions::paginate(100);

        return $this->sendSuccess($getTransaction, "loaded Successfully", Response::HTTP_OK);

    }

    public function getEcmiTrigger(){

        $getTransaction = Ecmsdb::paginate(100);

        return $this->sendSuccess($getTransaction, "loaded Successfully", Response::HTTP_OK);
    }


    public function getWhoisTrigger(){

        $Ewhois = Ewhois::paginate(100);

        return $this->sendSuccess($Ewhois, "loaded Successfully", Response::HTTP_OK);
    }


    public function makeitsmart(Request $request) {

        $baseUrl = env('MIDDLEWARE_URL');
        $addCustomerUrl = $baseUrl . 'verifymeter';
    
        $data = [
            'meter_number' => $request->meter_number,
            'vendtype' => $request->vendtype,
            'amount' => $request->amount,
        ];

       try{ 

        // $response = Http::withToken('Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18')->post($addCustomerUrl, [
        //     'meter_number' => $request->meter_number,
        //     'vendtype' => $request->vendtype
        // ]);

        $uuid = Str::uuid()->toString();
        $limitedUuid = str_replace('-', '', substr($uuid, 0, 15));


        $response = Http::withHeaders([
            'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
        ])->post($addCustomerUrl , $data);

         $newResponse =  $response->json();

        if($newResponse['message'] == "Completed"){

            $data = [
                "provider" => "IBEDC",
                "meterno"=> $newResponse['data']['meterNumber'],
                "vendtype" => $request->vendtype,
                "amount" => $request->amount,
                "custname" => $newResponse['data']['customerName'],
                "businesshub" => $newResponse['data']['businessUnit'],
                "custphoneno" => $newResponse['data']['phoneNumber'],
                "payreference" => StringHelper::generateTransactionReference() ?? $limitedUuid,
                "colagentid" => "IB001"
            ];

          return $this->runPreparationMigration($data);

        }else if($newResponse['status'] == "false"){
            return $newResponse['message'];
        }
        
        return $this->sendSuccess($newResponse, "loaded-Successfully", Response::HTTP_OK);
       

       }catch(\Exception $e){

        return $this->sendError($e->getmessage(), "No Result Found", Response::HTTP_BAD_REQUEST);

       }
        

    }




    private function runPreparationMigration($data){

       $baseUrl = env('MIDDLEWARE_URL');
       $addCustomerUrl = $baseUrl . 'vendelect';

        try {

            $response = Http::withHeaders([
                'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
            ])->post($addCustomerUrl , $data);

            $newResponse =  $response->json();

            if($newResponse['status'] == "true"){

                //Save Response to Warehouse. //Create a new database
               return $this->privatdeactiveallProcesse($newResponse);
            }else {
                return $newResponse;
            }
      
        }catch(\Exception $e){
            return $this->sendError('Error', "Error Initiating Payment: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

    }



    public function prepareIntegration(Request $request) {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'meter_number' => 'required|string',
            'amount' => 'required|numeric',
        ]);
    
        if ($validator->fails()) {
            return $this->sendError("Invalid input data", "Validation Error", Response::HTTP_BAD_REQUEST);
        }
    
        $checkMeter = ZoneECMI::where("MeterNo", $request->meter_number)->first();
    
        if (!$checkMeter) {
            return $this->sendError("No Record Found", "Error!", Response::HTTP_BAD_REQUEST);
        }

       // return $checkMeter;
    
        $uuid = Str::uuid()->toString();
        $limitedUuid = str_replace('-', '', substr($uuid, 0, 15));
    
        $meterNo = $checkMeter->MeterNo;
        $transReference = StringHelper::generateTransactionReference() ?? $limitedUuid;
        $amount = $request->amount;
        $merchant = "ABCD";
        $transDate = date("Y-m-d");
        $buid = $checkMeter->BUID;
        $mobile = isset($checkMeter->Mobile) ? $checkMeter->Mobile : "23458033426834";
    
        // Construct the URL
        $originalnotify = "http://192.168.15.156:9494/IBEDCWebServices/webresources/Payment/$meterNo/prepaid/113/$transReference/$amount/$merchant/$transDate/$buid/$mobile";
    
        try {
            $response = Http::post($originalnotify);
            // $response->json(); // Return the response content as JSON
             $newResponse =  $response->json();

             if($newResponse['transactionStatus'] == "success"){
                 //Save Response to Warehouse. //Create a new database
                return $this->processMainTransaction($newResponse);
             }else {
                return $newResponse;
            }
        } catch (\Exception $e) {
            // Return a structured error response
            return $this->sendError("Request failed", $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    private function processMainTransaction($data){

        //Save in Database
        $createData = MakeSave::create([
            'uniqueID' => $data['recieptNumber'],
            'amount' => $data['paidamount'],
            'unit' => $data['Units'],
            'transaction_ref' => $data['transactionReference'],
            'account_no' => $data['customer']['accountNumber'],
            'meter_no' => $data['customer']['meterNumber'],
            'name' => $data['customer']['customerName'],
            'ecmi_ref' => $data['transactionReference'],
        ]);

        $checkTExist = ECMITransactions::where("transref", $data['transactionReference'])->first();

        $updateTransanID = MakeSave::where("ecmi_ref", $data['transactionReference'])->update([
            'transactdno' => $checkTExist->TransactionNo
        ]);

       
        try{

        // Disable the trigger for the specific connection
        DB::connection('ecmi_prod')->unprepared('DISABLE TRIGGER [TRANSACTION_TRIGGER] ON [ECMI].[dbo].[Transactions]');

        $checkSubAccount = SubAccountPayment::where("TransactionNo",  $checkTExist->TransactionNo)->first();
        if($checkSubAccount){  $checkSubAccount->delete(); }

        if( $checkTExist){  $checkTExist->delete(); }

         //Delete token from paymentTransaction
        $checkpaymentTrans = ECMIPaymentTransaction::where("transref", $data['transactionReference'])->first();
        if($checkpaymentTrans){  $checkpaymentTrans->delete(); }

        //Delete Token from token log // TO FIX THIS
        $trimSpaces = str_replace(' ', '', $data['recieptNumber']);
        $tokeLogs = ECMITokenLogs::where("Token", $trimSpaces)->first();
        if($tokeLogs){  $tokeLogs->delete(); }
        

        $trigD = Ecmsdb::where("Token_before",  $trimSpaces)->first();
        if($trigD){ $trigD->delete(); }
        
        //Delete from whoisActive
        //DELETE FROM  [msdb].[dbo].[WhoIsActive] where Login_name = 'distributor_piq'
        $whoisActive = Ewhois::where("Login_name", "distributor_piq")->first();
        if($whoisActive){  $whoisActive->delete(); }

           
        //[HoldModeTransactions] Delete Token if Exists  // check this
        $HoldMode = ECMIHoldMode::where("Token", $trimSpaces)->first();
        if($HoldMode){ HoldMode->delete(); }

          // Enable the trigger again for the same connection
         DB::connection('ecmi_prod')->unprepared('ENABLE TRIGGER [TRANSACTION_TRIGGER] ON [ECMI].[dbo].[Transactions]');


############################################### FIX THE ISSUES OF OTHER TABLES ADDED ##########################################################################

        //Check Audit
        $subAudSubPayment = AuditSubAccountPayment::where("TransactionNo",  $checkTExist->TransactionNo)->first();
        if($subAudSubPayment){  $subAudSubPayment->delete(); }

        $tokenAudLog = AuditTokenLog::where("Token", $trimSpaces)->first();
        if($tokenAudLog){  $tokenAudLog->delete(); }

        $auditTrans = AuditTransaction::where("Token", $trimSpaces)->first();
        if( $auditTrans){  $auditTrans->delete(); }


        $auditpTransaction = AuditPaymentTransaction::where("transref", $data['transactionReference'])->first();
        if($auditpTransaction){  $auditpTransaction->delete(); }

        $checkAduitnew = AuditNewTransactions::where("transref", $data['transactionReference'])->first();
        if( $checkAduitnew){  $checkAduitnew->delete(); }

        $logDeletedTrans = LogDeletedTransactions::where("transactionno", $checkTExist->TransactionNo)->first();
        if($logDeletedTrans){  $logDeletedTrans->delete(); }

        $logSubApayment = LogSubAccountPayments::where("TransactionNo", $checkTExist->TransactionNo)->first();
        if($logSubApayment){  $logSubApayment->delete(); }

        $logInserted = LogInsertSubAccountDeductions::where("transactionno", $checkTExist->TransactionNo)->first();
        if($logInserted){  $logInserted->delete(); }


############################################### FIX THE ISSUES OF OTHER TABLES ADDED ##########################################################################



         return $this->sendSuccess($createData, "loaded-Successfully". ' '. $trimSpaces, Response::HTTP_OK);

        }catch(\Exception $e){
           
            return $this->sendError('Error', "Error Initiating Payment: ". $e->getMessage(), Response::HTTP_BAD_REQUEST);

        }
        
     

    }



    // private function privatdeactiveallProcesse($data){


       
    //     //Save in Database
    //     $createData = MakeSave::create([
    //         'uniqueID' => $data['token'],
    //         'amount' => $data['paidamount'],
    //         'unit' => $data['Units'],
    //         'transaction_ref' => $data['transactionReference'],
    //         'account_no' => $data['customer']['accountNumber'],
    //         'meter_no' => $data['customer']['meterNumber'],
    //         'name' => $data['customer']['customerName'],
    //         'ecmi_ref' => $data['reference'],
    //     ]);

    //     //Delete Result from Middleware 
    //      $middlewareTrans = Transactions::where("reference", $data['reference'])->first();
    //      if($middlewareTrans){  $middlewareTrans->forceDelete(); }

    //     // //Delet from Reflog
    //     $middlewareRefLog = Resplog::where("reference", $data['reference'])->first();
    //     if($middlewareRefLog){ $middlewareRefLog->forceDelete();}


    //     try{

    //     // Disable the trigger for the specific connection
    //     DB::connection('ecmi_prod')->unprepared('DISABLE TRIGGER [TRANSACTION_TRIGGER] ON [ECMI].[dbo].[Transactions]');

    //       //  return  $createData;
    //     //Delete token from Transaction Table,
    //     $checkTExist = ECMITransactions::where("transref", $data['transactionReference'])->first();

    //     $checkSubAccount = SubAccountPayment::where("TransactionNo",  $checkTExist->TransactionNo)->first();
    //     if($checkSubAccount){  $checkSubAccount->delete(); }

    //     if( $checkTExist){  $checkTExist->delete(); }

    //      //Delete token from paymentTransaction
    //     $checkpaymentTrans = ECMIPaymentTransaction::where("transref", $data['transactionReference'])->first();
    //     if($checkpaymentTrans){  $checkpaymentTrans->delete(); }

    //     //Delete Token from token log
    //     $trimSpaces = str_replace(' ', '', $data['token']);
    //     $tokeLogs = ECMITokenLogs::where("Token", $trimSpaces)->first();
    //     if($tokeLogs){  $tokeLogs->delete(); }
        

    //     $trigD = Ecmsdb::where("Token_before",  $trimSpaces)->first();
    //     if($trigD){ $trigD->delete(); }
        
    //     //Delete from whoisActive
    //     //DELETE FROM  [msdb].[dbo].[WhoIsActive] where Login_name = 'distributor_piq'
    //     $whoisActive = Ewhois::where("Login_name", "distributor_piq")->first();
    //     if($whoisActive){  $whoisActive->delete(); }

           
    //     //[HoldModeTransactions] Delete Token if Exists  // check this
    //     $HoldMode = ECMIHoldMode::where("Token", $trimSpaces)->first();
    //     if($HoldMode){ HoldMode->delete(); }

    //       // Enable the trigger again for the same connection
    //      DB::connection('ecmi_prod')->unprepared('ENABLE TRIGGER [TRANSACTION_TRIGGER] ON [ECMI].[dbo].[Transactions]');

    //      return $this->sendSuccess($createData, "loaded-Successfully". ' '. $trimSpaces, Response::HTTP_OK);

    //     }catch(\Exception $e){
           
    //         return $this->sendError('Error', "Error Initiating Payment: ". $e->getMessage(), Response::HTTP_BAD_REQUEST);

    //     }
        
     

    // }




}
