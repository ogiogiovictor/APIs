<?php

namespace Bitfumes\Setup\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseApiController;
use Bitfumes\Setup\Models\ZoneECMI;
use App\Helpers\StringHelper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Bitfumes\Setup\Models\MakeSave;
use Bitfumes\Setup\Models\ECMITransactions;
use Bitfumes\Setup\Models\SubAccountPayment;
use Bitfumes\Setup\Models\ECMIPaymentTransaction;
use Bitfumes\Setup\Models\ECMITokenLogs;
use Bitfumes\Setup\Models\Ecmsdb;
use Bitfumes\Setup\Models\ECMIHoldMode;
use Bitfumes\Setup\Models\AuditSubAccountPayment;
use Bitfumes\Setup\Models\AuditTokenLog;
use Bitfumes\Setup\Models\AuditTransaction;
use Bitfumes\Setup\Models\AuditPaymentTransaction;
use Bitfumes\Setup\Models\AuditNewTransactions;
use Bitfumes\Setup\Models\LogDeletedTransactions;
use Bitfumes\Setup\Models\LogSubAccountPayments;
use Bitfumes\Setup\Models\LogInsertSubAccountDeductions;

class PrepareSetupController extends BaseApiController 
{

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

        try {

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

             //[HoldModeTransactions] Delete Token if Exists  // check this
            $HoldMode = ECMIHoldMode::where("Token", $trimSpaces)->first();
            if($HoldMode){ $HoldMode->delete(); }

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


        return $this->sendSuccess($createData, "loaded-Successfully". ' '. $trimSpaces, Response::HTTP_OK);
        
        }catch(\Exception $e){

           
            return $this->sendError('Error', "Error Initiating Payment: ". $e->getMessage(), Response::HTTP_BAD_REQUEST);

        }
        


    }
}