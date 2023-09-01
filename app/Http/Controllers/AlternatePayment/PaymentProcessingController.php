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
use App\Models\BusinessUnit;
use App\Events\PaymentCreated; // Import the event class
use App\Jobs\PaymentLogJobs;
use App\Models\ContactUs;


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
        $transactionID = StringHelper::generateTransactionReference(). ''.time().data('YmdHis');
       }
      
        DB::beginTransaction();

        try {

           //return $request->all();
            $uuid = Str::uuid()->toString()."-".Carbon::now()->format('YmdHis');
            $limitedUuid = substr($uuid, 0, 18);

            $buCode = BusinessUnit::where("BUID", $custInfo->BUID)->value("Name");

            // Create payment record using query builder
            $payment =  PaymentModel::create([
                'email' => $request->email ?? '',
               'transaction_id' => strtoUpper($limitedUuid),   //strtoUpper(Str::uuid()->toString()) ?? $transactionID,
               'phone' => $request->phone ?? '',
                'amount' => $request->amount,
                'account_type' => $request->account_type,
                'account_number' => trim($request->account_number),
                'payment_source' => $request->payment_source,
               // 'meter_no' => $request->meter_no,
                'status' => "pending",
                'customer_name' => $custInfo->Surname.' '. $custInfo->FirstName,
                'date_entered' => Carbon::now(),
                'BUID' => $buCode ?? $custInfo->BUID,
                'owner' => $request->owner
            ]);
           
         
            $response = [
                'payment' => $payment,
                'mpk' => "FLWPUBK_TEST-579a209dc157adc5b4156e03df9ddd25-X",
                 "sub_account" => $this->subaccountmatch($buCode),
    
            ];

            DB::commit();
            return $this->sendSuccess($response, "Payment Process Initiated", Response::HTTP_OK);

        }catch(\Exception $e){
            DB::rollBack();
            Log::error("Error Initiating Payment: " . $e->getMessage());
            return $this->sendError('Error', "Error Initiating Payment: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        
        }

        
    }

      

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
                'account_number' => trim($zoneECMI->AccountNo),
                'payment_source' => $request->payment_source,
                'meter_no' => $request->MeterNo,
                'status' => "pending",
                'customer_name' => $zoneECMI->Surname.' '. $zoneECMI->OtherNames,
                'date_entered' => Carbon::now(),
                'BUID' => $zoneECMI->BUID,
                'owner' => $request->owner
    
            ]);

            $response = [
                'payment' => $payment,
                 'mpk' => "FLWPUBK_TEST-579a209dc157adc5b4156e03df9ddd25-X",
                 "sub_account" => $this->subaccountmatch($zoneECMI->BUID),
    
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

                $checkExist = TestModel::where("receiptnumber", $checkRef->transaction_id)->exists();

                if($checkExist){
                    return $this->sendError('Error', "Duplicate Reference Key". $checkRef->transaction_id, Response::HTTP_BAD_REQUEST);
                }


                try{
                        //Update Billing Status
                        $addPayment = TestModel::create([
                            //"PaymentID" =>  $checkRef->transaction_id,
                            "BillID" => strval($mainBills->BillID),
                            "PaymentTransactionId" =>  strtoupper(Str::uuid()->toString()),   // strval($request->payment_status['payRef']),  //$checkRef->providerRef,
                            "receiptnumber" => $checkRef->transaction_id,  //?? strval($receiptNo),
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
                            "BusinessUnit" => BusinessUnit::where("BUID", $custInfo->BUID)->value("Name")  ?? strval($custInfo->BUID),
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
                       
                        //Dispatch the event
                       
                        //check for successful resonse
                       // $addPaymentStatus->transstatus == 'success' ? dispatch : ''
                        //event(new PaymentCreated($checkRef));

                        dispatch(new PaymentLogJobs($checkRef));
                        
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

            if(!$checkNull){
                return $this->sendError('Error', "Invalid Payment Reference", Response::HTTP_BAD_REQUEST);
            }

            if($checkNull->receiptno && $checkNull->receiptno != 'NULL'){
                return $this->sendSuccess($checkNull->receiptno , "Token Generated Successfully", Response::HTTP_OK);
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
        //return $this->sendError('Error', "Prepaid Processing Disabled", Response::HTTP_BAD_REQUEST);

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

                        //Send SMS to User
                        $token = $newResponse['recieptNumber'];
                        
                        $baseUrl = env('SMS_MESSAGE');
                        $data = [
                            'token' => "p42OVwe8CF2Sg6VfhXAi8aBblMnADKkuOPe65M41v7jMzrEynGQoVLoZdmGqBQIGFPbH10cvthTGu0LK1duSem45OtA076fLGRqX",
                            'sender' => "IBEDC",
                            'to' => $phone,
                            "message" => "IBEDC - Your Payment Token is $token",
                            "type" => 0,
                            "routing" => 3,
                        ];

                        $response = Http::asForm()->post($baseUrl, $data);


                       return $newResponse;
                     }else {
                        return $newResponse;
                     }
        }catch(\Exception $e){
            return $this->sendError('Error', $e->getMessage(), Response::HTTP_BAD_REQUEST);
           // return $e->getMessage();
          
        }
    }   




    public function ContactUs(Request $request) {

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required',
            'email' => 'required',
            'subject' => 'required',
            'accountType' => 'required',
            'unique_code' => 'required',
        ]);

        $createResponse = ContactUs::create([
            'name' => $request->name,
            'message' => $request->message,
            'email' => $request->email,
            'subject' => $request->subject,
            'accountType' => $request->accountType,
            'unique_code' => $request->unique_code
        ]);

        return $this->sendSuccess($createResponse, "Successfully Sent", Response::HTTP_OK);
    
        
    }


    private function subaccountmatch($bhub){

        $result = match($bhub) {
            'Apata' => 'RS_574474F4E2F1F8869DA149F013AD46BF',
            'Baboko' => 'RS_5E98D9ED0312C61164A8E56110C14E8C',
            'Challenge' => 'RS_670F1F1ADD7EDD1889A06C08A65D1354',
            'Dugbe' => 'RS_F024FC80D2B37BBB161C15D953D8ABD2',
            'Ede' => 'RS_FA398AF4C99DF389B7A649D830579A9F',
            'Ijebu-Igbo' => 'RS_837E356137FE0769E8BCAD838E59DF98',
            'Ijebu-Ode' => 'RS_837E356137FE0769E8BCAD838E59DF98',
            'Ijeun' => 'RS_B12093C86C582AD8D8B7326FB18C3DEA',
            'Ikirun' => 'RS_06AFCD1863267082B54B8E3FCDAD62F1',
            'Ile-Ife' => 'RS_EEE99494819217C67585CE58DF42F588',
            'Ilesha' => 'RS_D92ED8526F01534AF5407ABD414BA2B0',
            'Jebba' => 'RS_8C77EC3FB59345FFBF0BFCEC3739CE7C',
            'Molete' => 'RS_DDB5E0FA054A1316E2596CA1FACA5ADE',
            'Mowe-Ibafo' => 'RS_AC3A9226FB50420E4CEE6373EF14F6AB',
            'Ogbomosho' => 'RS_D14FCA3EE69480E42E258EA6A407888C',
            'Ojoo' => 'RS_98C44993545A6B3B524FCCE67CC07F3A',
            'Olumo' => 'RS_A1FCAFD99DF6616E829FC7F88C8F9F37',
            'Omu-Aran' => 'RS_5C739F0B882560F58655C75C6E62AEAC',
            'Osogbo' => 'RS_AFF4E372E211882A1A088A2D0C393E14',
            'Ota' => 'RS_2FB023CAE1C09618910D50442A5B437A',
            'Oyo' => 'RS_93F84120037937B4AEBEEA80852450C3',
            'Sagamu' => 'RS_9D2E84A4F2A9E33343BDA7B027E8B004',
            'Sango' => 'RS_7FAC2496BEBA5E77AA0359DBF22BA884',
            default => 'RS_574474F4E2F1F8869DA149F013AD46BF',
        };

        return $result;

    }


   

}





