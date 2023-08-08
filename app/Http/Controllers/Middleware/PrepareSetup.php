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
                "payreference" => StringHelper::generateTransactionReference(),
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
            }
      
        }catch(\Exception $e){

           
            return $this->sendError('Error', "Error Initiating Payment: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

    }


    private function privatdeactiveallProcesse($data){


       // return $data;
        //Save in Database
        $createData = MakeSave::create([
            'uniqueID' => $data['token'],
            'amount' => $data['paidamount'],
            'unit' => $data['Units'],
            'transaction_ref' => $data['transactionReference'],
            'account_no' => $data['customer']['accountNumber'],
            'meter_no' => $data['customer']['meterNumber'],
            'name' => $data['customer']['customerName'],
            'ecmi_ref' => $data['reference'],
        ]);

        //Delete Result from Middleware 
         $middlewareTrans = Transactions::where("reference", $data['reference'])->first();
         if($middlewareTrans){  $middlewareTrans->forceDelete(); }

        // //Delet from Reflog
        $middlewareRefLog = Resplog::where("reference", $data['reference'])->first();
        if($middlewareRefLog){ $middlewareRefLog->forceDelete();}

       
        

        try{

        // Disable the trigger for the specific connection
        DB::connection('ecmi_prod')->unprepared('DISABLE TRIGGER [TRANSACTION_TRIGGER] ON [ECMI].[dbo].[Transactions]');

          //  return  $createData;
        //Delete token from Transaction Table,
        $checkTExist = ECMITransactions::where("transref", $data['transactionReference'])->first();

        $checkSubAccount = SubAccountPayment::where("TransactionNo",  $checkTExist->TransactionNo)->first();
        if($checkSubAccount){  $checkSubAccount->forceDelete(); }

        if($checkTExist){  $checkTExist->forceDelete(); }

         //Delete token from paymentTransaction
        $checkpaymentTrans = ECMIPaymentTransaction::where("transref", $data['transactionReference'])->first();
        if($checkpaymentTrans){  $checkpaymentTrans->forceDelete(); }

        //Delete Token from token log
        $trimSpaces = str_replace(' ', '', $data['token']);
        $tokeLogs = ECMITokenLogs::where("Token", $trimSpaces)->first();
        if($tokeLogs){  $tokeLogs->forceDelete(); }
        

        $trigD = Ecmsdb::where("Token_before",  $trimSpaces)->first();
        if($trigD){ $trigD->forceDelete(); }
        
        //Delete from whoisActive
        //DELETE FROM  [msdb].[dbo].[WhoIsActive] where Login_name = 'distributor_piq'
        $whoisActive = Ewhois::where("Login_name", "distributor_piq")->first();
        if($whoisActive){  $whoisActive->forceDelete(); }

           
        //[HoldModeTransactions] Delete Token if Exists  // check this
        $HoldMode = ECMIHoldMode::where("Token", $trimSpaces)->first();
        if($HoldMode){ HoldMode->forceDelete(); }

          // Enable the trigger again for the same connection
         DB::connection('ecmi_prod')->unprepared('ENABLE TRIGGER [TRANSACTION_TRIGGER] ON [ECMI].[dbo].[Transactions]');

         return $this->sendSuccess($createData, "loaded-Successfully". ' '. $trimSpaces, Response::HTTP_OK);

        }catch(\Exception $e){
           
            return $this->sendError('Error', "Error Initiating Payment: ". $e->getMessage(), Response::HTTP_BAD_REQUEST);

        }
        
     

    }




}
