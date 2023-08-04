<?php

namespace App\Http\Controllers\Middleware;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Middleware\Transactions;
use App\Models\Middleware\Resplog;
use App\Models\Middleware\Ecmsdb;
use App\Models\Middleware\Ewhois;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Helpers\StringHelper;

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

      // DB::beginTransaction();
        try {

            $response = Http::withHeaders([
                'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
            ])->post($addCustomerUrl , $data);

            $newResponse =  $response->json();

            if($newResponse['status'] == "true"){
                
                //Save Response to Warehouse. //Create a new database
                $this->deactiveallProcess($newResponse);
            }
        // DB::commit();

           

        }catch(\Exception $e){

            DB::rollBack();
            return $this->sendError('Error', "Error Initiating Payment: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

    }


    private function privatdeactiveallProcesse($data){

        //Delete Result from Middleware 

        //Deactiveate Triggers
         // Disable the trigger for the specific connection
        DB::connection('ecmi_prod')->unprepared('DISABLE TRIGGER [TRANSACTION_TRIGGER] ON [ECMI].[dbo].[Transactions]');

        //Delete token from Transaction Table,
        //Delete Token from token log
        //Delete token from paymentTransaction

        
        //Delete from whoisActive
        //DELETE FROM  [msdb].[dbo].[WhoIsActive] where Login_name = 'distributor_piq'
        
        //[HoldModeTransactions] Delete Token if Exists
       // [MeterMDTransactions] Delete Token if Exists
        //[meterMDtransactions_BAK] Delete Token if Exists

        //[log_deletedtransactions]	transactionno  Delete if Neccesary
        // logs_InsertSubAccountDeductions	[transactionno] Delete if Neccessary





         

       // Enable the trigger again for the same connection
         DB::connection('ecmi_prod')->unprepared('ENABLE TRIGGER [TRANSACTION_TRIGGER] ON [ECMI].[dbo].[Transactions]');

    }




}
