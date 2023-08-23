<?php

namespace App\Http\Controllers\AlternatePayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\PaymentRequest;
use App\Models\TestModel;
use App\Models\PaymentModel;
use App\Helpers\StringHelper;
use App\Models\ZoneECMI;
use App\Models\ZoneCustomer;
use App\Models\ZoneBills;
use App\Models\PaymentSource;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\TestModelPayments;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\AuditLog;


class PaymentProcessingController extends BaseApiController
{

   
    public function makePayment(PaymentRequest $request){
       

        try {

            if($request->account_type == "Postpaid"){

               return $payment = $this->createPostPayment($request);

            } elseif ($request->account_type == "Prepaid") {

               return $payment = $this->createPrePayment($request);

            } else {

                return $this->sendError('Error', "Invalid Account Type", Response::HTTP_BAD_REQUEST);
            }

        }catch(\Exception $e){

          
            return $this->sendError('Error', "Error Initiating Payment: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        
        }

    }


    private function createPostPayment($request){
       $custInfo = ZoneCustomer::where("AccountNo", $request->account_number)->first();

        if(!$custInfo){
            return $this->sendError('Error', "No Record Found", Response::HTTP_BAD_REQUEST);
        }

       $transactionID = StringHelper::generateTransactionReference();

       $checkTransaID = PaymentModel::where("transaction_id",  $transactionID)->value("transaction_id");

       if($checkTransaID){
        $transactionID = StringHelper::generateTransactionReference(). ''.time().data('Ymd');
       }
      
        DB::beginTransaction();

        try {

           //return $request->all();
            $uuid = Str::uuid()->toString();
            $limitedUuid = substr($uuid, 0, 18);

            // Create payment record using query builder
            $payment =  PaymentModel::create([
                'email' => $request->email ?? '',
               'transaction_id' => strtoUpper($limitedUuid),   //strtoUpper(Str::uuid()->toString()) ?? $transactionID,
               'phone' => $request->phone ?? '',
                'amount' => $request->amount,
                'account_type' => $request->account_type,
                'account_number' => $request->account_number,
                'payment_source' => $request->payment_source,
               // 'meter_no' => $request->meter_no,
                'status' => "pending",
                'customer_name' => $custInfo->Surname.' '. $custInfo->FirstName,
                'date_entered' => Carbon::now(),
                'BUID' => $custInfo->BUID
            ]);
           
         
            $response = [
                'payment' => $payment,
                'payment_key_authData' => "G3cf/VTtAHCdHZNxc5GXWRI8z5P0goL2amXWDVFgb6D3XK/QMtZW90TYdl5zffDCNpiZThJzk0+eEU/Y/aYS6fyIOpQZGFrOr8hmvx5869sl2kr5u8qjnM7q5b4ZnTqdKDLtNxr3Qr7anj6YLpox1FOsiyT26mktXL+7SFOaZ15NMtne1z4xrj4R2SndowI/Znsapo7Gfzvp+L7XJyQ8kLYYRk3INjvmRPPQoJg1R0Nnh6EQE3ldIdwylB7GKtr6a71N/yCd4ZtyIcqq1ZNzdWcZyy5eEBAlDIxuECdBqH6hRq2/RbkfARqidNN4Kq0WviSRaRYGbiNjl2W9pNcM8g==",
                'currency' => "NGN",
                 "mcode" => "MX19329",
                 "pay_item_id" => "Default_Payable_MX19329",
    
            ];

            DB::commit();
            return $this->sendSuccess($response, "Payment Process Initiated", Response::HTTP_OK);

        }catch(\Exception $e){
            DB::rollBack();
            Log::error("Error Initiating Payment: " . $e->getMessage());
            return $this->sendError('Error', "Error Initiating Payment: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        
        }

        
    }



    /* {
        "provider": "IBEDC",
        "meterno": "70004984442",
        "amount": "1",
        "vendtype": "Prepaid",              
        "custname": "OMOWELE ADEKUMBI FUNMILAYO 16",
        "businesshub": "Molete",
        "custphoneno": "2348062665117", 
        "payreference": "MDWTESTLIVE-1",
        "colagentid": "IB001" // leave
      }
      */

      

      private function createPrePayment($request) {


        $zoneECMI = ZoneECMI::where("MeterNo", $request->MeterNo)->first();
      

        if(!$zoneECMI){
            return $this->sendError('Error', "No Record Found", Response::HTTP_BAD_REQUEST);
        }

        $transactionID = StringHelper::generateTransactionReference();
      
        DB::beginTransaction();

        try {

            $uuid = Str::uuid()->toString();
            $limitedUuid = substr($uuid, 0, 18);

            $payment =  PaymentModel::create([
                'email' => $request->EMail ?: $request->email,
                'transaction_id' => strtoUpper($limitedUuid),   //strtoUpper(Str::uuid()->toString()) ?? $transactionID,
                'phone' => $request->Mobile ?? $request->phone,
                'amount' => 1, // $request->amount,
                'account_type' => $request->account_type,
                'account_number' => $zoneECMI->AccountNo,
                'payment_source' => $request->payment_source,
                'meter_no' => $request->MeterNo,
                'status' => "pending",
                'customer_name' => $zoneECMI->Surname.' '. $zoneECMI->OtherNames,
                'date_entered' => Carbon::now(),
                'BUID' => $zoneECMI->BUID
    
            ]);

            $response = [
                'payment' => $payment,
                'payment_key_authData' => "G3cf/VTtAHCdHZNxc5GXWRI8z5P0goL2amXWDVFgb6D3XK/QMtZW90TYdl5zffDCNpiZThJzk0+eEU/Y/aYS6fyIOpQZGFrOr8hmvx5869sl2kr5u8qjnM7q5b4ZnTqdKDLtNxr3Qr7anj6YLpox1FOsiyT26mktXL+7SFOaZ15NMtne1z4xrj4R2SndowI/Znsapo7Gfzvp+L7XJyQ8kLYYRk3INjvmRPPQoJg1R0Nnh6EQE3ldIdwylB7GKtr6a71N/yCd4ZtyIcqq1ZNzdWcZyy5eEBAlDIxuECdBqH6hRq2/RbkfARqidNN4Kq0WviSRaRYGbiNjl2W9pNcM8g==",
                'currency' => "NGN",
                 "mcode" => "MX19329",
                 "pay_item_id" => "Default_Payable_MX19329",
    
            ];

            DB::commit();

            return $this->sendSuccess($response, "Payment Process Initiated", Response::HTTP_OK);

        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Error', "Error Initiating Payment: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }


    }




    public function processPayment(Request $request){
      
        if(!$request->payment_status){
            return $this->sendError('Error', "Error Initiating Payment", Response::HTTP_BAD_REQUEST);
        }

        $checkRef =  PaymentModel::where("transaction_id", $request->payment_status['txnref'])->first();

    
       //return $checkRef;
       if($checkRef && $request->payment_status['payRef'] && $request->payment_status['resp']){

            if($checkRef->account_type == 'Postpaid'){

                $custInfo = ZoneCustomer::where("AccountNo", $checkRef->account_number)->first();
                $mainBills = ZoneBills::where('AccountNo', $checkRef->account_number)->latest('BillDate')->first();

                $receiptNo = Carbon::now()->format('YmdHis');  //YmdHisu


                try{

                      //Update Billing Status
                $addPayment = TestModel::create([
                    //"PaymentID" =>  $checkRef->transaction_id,
                    "BillID" => strval($mainBills->BillID),
                    "PaymentTransactionId" =>  strtoupper(Str::uuid()->toString()),   // strval($request->payment_status['payRef']),  //$checkRef->providerRef,
                    "receiptnumber" => $checkRef->transaction_id ?? strval($receiptNo),
                    "PaymentSource" => 101,
                    "MeterNo" => strval($custInfo->MeterNo) ?? 'NULL',
                    "AccountNo" => strval($custInfo->AccountNo) ?? 'NULL',
                    "PayDate" =>  strval(Carbon::now()->format('Y-m-d H:i:s')),
                    "PayMonth" => Carbon::now()->format('m'),
                    "PayYear" => Carbon::now()->format('Y'),
                    "OperatorID" => 1,
                    "TotalDue" => 0.00,
                    "Payments" => $request->payment_status['apprAmt'],
                    //"Balance" => 'NULL',
                    "Processed" => 0,
                    //"ProcessedDate" => 'NULL',
                    "BusinessUnit" => strval($custInfo->BUID),
                    "DateEngtered" => strval(Carbon::now()->format('Y-m-d H:i:s')),
                    "CustomerID" => strval($custInfo->CustomerID),
                ]);

                $addPaymentStatus = TestModelPayments::create([
                     'transid' =>  strval($addPayment->PaymentTransactionId),
                     'transref' =>  $checkRef->transaction_id,
                     'enteredby' => $checkRef->payment_source,  //An account to be created by Joseph
                     'transdate' => Carbon::now()->format('Y-m-d H:i:s'),
                     'transamount' => $request->payment_status['apprAmt'],
                     'transstatus' =>  $request->payment_status['resp'] == '00' ?  'success' : 'failed', 
                     'accountno' => $checkRef->account_number,
                     'transactionresponsemessage' => $request->payment_status['payRef'],
                     'paymenttype' => 3, //We need to check this later,
                     'TransactionBusinessUnit' => $custInfo->BUID,
                 ]);

                 $update = PaymentModel::where("transaction_id", $checkRef->transaction_id)->update([
                    'status' => $request->payment_status['resp'] == '00' ?  'success' : 'failed', //"resp": "00",
                    'provider' => $request->payment_status['provider'] ?? '',
                    'providerRef' => $request->payment_status['payRef'],
                    'receiptno' =>  $receiptNo,  //Carbon::now()->format('YmdHis').time()
                    'BUID' => $custInfo->BUID,
                    'Descript' => $request->payment_status['desc'],
                ]);

                 //I still have one table to add here
                 return $this->sendSuccess($addPayment, "Payment Successfully Completed", Response::HTTP_OK);


                }catch(\Exception $e){

                    return $this->sendError('Error', $e, Response::HTTP_BAD_REQUEST);

                }
               
                
               
            
            } else if($checkRef->account_type == 'Prepaid'){ 

               
                $zoneECMI = ZoneECMI::where("MeterNo", $request->payment_status['MeterNo'])->first();

                if(!$request->payment_status['MeterNo'] || !$request->payment_status['account_type'] || !$request->payment_status['phone'] || !$request->payment_status['amount']){

                    return $this->sendError('Error', "Please send all required information", Response::HTTP_BAD_REQUEST);
                }

                if(!$zoneECMI){
                    return $this->sendError('Error', "Meter Information Not Found", Response::HTTP_BAD_REQUEST);
                }

                $update = PaymentModel::where("transaction_id", $checkRef->transaction_id)->update([
                    'providerRef' => $request->payment_status['payRef'],
                ]);

               $amount = 1; //$request->amount; // please remove this on live later and add the amount request to the generate token;
               $customerName = $zoneECMI->Surname.' '. $zoneECMI->OtherNames;
               $phone = $request->payment_status['phone']  ?? $zoneECMI->Mobile;

               return $this->generateToken($request->payment_status['MeterNo'], $request->payment_status['account_type'], 
               $amount, "IBEDC", $customerName, $zoneECMI->BUID, $phone, $checkRef->transaction_id);

               /* try {
                   

                    $baseUrl = env('MIDDLEWARE_URL');
                    $addCustomerUrl = $baseUrl . 'vendelect';

                    $data = [
                            'meterno' => $request->payment_status['MeterNo'],
                            'vendtype' => $request->payment_status['account_type'],
                            'amount' => 1, //$request->amount,
                            "provider" => "IBEDC",
                            "custname" => $zoneECMI->Surname.' '. $zoneECMI->OtherNames,
                            "businesshub" => $zoneECMI->BUID,
                            "custphoneno" => $request->payment_status['phone']  ?? $zoneECMI->Mobile,
                            "payreference" => StringHelper::generateTransactionReference(),
                            "colagentid" => "IB001",
        
                        ];
                
        
                        $response = Http::withHeaders([
                            'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
                        ])->post($addCustomerUrl , $data);
                
                         $newResponse =  $response->json();
        
                         if($newResponse['status'] == "true"){                 
        
                            $update = PaymentModel::where("transaction_id", $checkRef->transaction_id)->update([
                                'status' => $newResponse['status'] == "true" ?  'success' : 'failed', //"resp": "00",
                                'provider' => "IBEDC-MIDDLEWARE" ?? '',
                                'providerRef' => $newResponse['transactionReference'],
                                'receiptno' =>   $newResponse['recieptNumber'],  //Carbon::now()->format('YmdHis').time()
                                'BUID' => $zoneECMI->BUID,
                                'Descript' => $newResponse['message'],
                            ]);
        
                            return $newResponse;
        
                         }else {
                            return $newResponse;
                        }
                    

                  
                }catch(\Exception $e){
                    return $this->sendError('Error', $e->getMessage(), Response::HTTP_BAD_REQUEST);
                }
                */

            }
        

      } else {
        return $this->sendError('Error', "Error Processing Payment", Response::HTTP_BAD_REQUEST);
      }

       
    }



    public function retryPayment(Request $request){

        $retryToken = $request->providerRef;

        if(!$retryToken){
            return $this->sendError('Error', "Please provide payment reference", Response::HTTP_BAD_REQUEST);
        }

        try {

            $checkNull = PaymentModel::where("providerRef", $retryToken)->first();

            if($checkNull->receiptno && $checkNull->receiptno !== 'NULL'){
                return $this->sendSuccess( $checkNull->receiptno , "Token Generated Successfully", Response::HTTP_OK);
            }else {
                //Use here to generate token
                return $this->generateToken($checkNull->meter_no, $checkNull->account_type, 
                $checkNull->amount, "IBEDC", $checkNull->customer_name, $checkNull->BUID, $checkNull->phone, $checkNull->transaction_id);

            }

        }catch(\Exception $e){
            return $this->sendError('Error', $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }



    public function paymentSource() {

      $paySource = PaymentSource::all();
      return $this->sendSuccess($paySource, "PaymentSource Successfully Loaded", Response::HTTP_OK);

    }



    private function generateToken($meterNo, $accountType, $amount, $provider, $customerName, $buid, $phone, $transactionID){

       try {
                $baseUrl = env('MIDDLEWARE_URL');
                $addCustomerUrl = $baseUrl . 'vendelect';

                $data = [
                        'meterno' => $meterNo,
                        'vendtype' => $accountType,
                        'amount' => 1, //$amount,
                        "provider" => "IBEDC",
                        "custname" => $customerName,
                        "businesshub" => $buid,
                        "custphoneno" => $phone,
                        "payreference" => StringHelper::generateTransactionReference(),
                        "colagentid" => "IB001",

                    ];
            

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
                    ])->post($addCustomerUrl , $data);
            
                    $newResponse =  $response->json();

                    if($newResponse['status'] == "true"){                 

                        $update = PaymentModel::where("transaction_id", $transactionID)->update([
                            'status' => $newResponse['status'] == "true" ?  'success' : 'failed', //"resp": "00",
                            'provider' => "IBEDC-MIDDLEWARE" ?? '',
                            'providerRef' => $newResponse['transactionReference'],
                            'receiptno' =>   $newResponse['recieptNumber'],  //Carbon::now()->format('YmdHis').time()
                            'BUID' => $buid,
                            'Descript' => $newResponse['message'],
                        ]);

                       return $newResponse;
                     }else {
                        return $newResponse;
                     }
        }catch(\Exception $e){
            return $this->sendError('Error', $e->getMessage(), Response::HTTP_BAD_REQUEST);
           // return $e->getMessage();
          
        }
    }   







   

}





