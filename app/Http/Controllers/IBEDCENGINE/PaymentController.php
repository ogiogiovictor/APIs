<?php

namespace App\Http\Controllers\IBEDCENGINE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ECMIPayment;
use App\Models\ZonePayments;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\EcmiPaymentResource;
use App\Http\Resources\ZoneResource;
use App\Models\ZoneBills;

class PaymentController extends BaseApiController
{
    public function getPayments(){

      
        $newpayment = new ECMIPayment();
        
        //$ecmi_payment = $newpayment->paymentCount();
        //$ems_payment = ZonePayments::count();

        $ecmi_payment = $newpayment->whereYear('TransactionDateTime', '=', now()->year)
                                          ->whereMonth('TransactionDateTime', '=', now()->month)
                                          ->sum('Amount');

        $ems_payment = ZonePayments::whereYear('PayYear', '=', now()->year)
                                          ->whereMonth('PayMonth', '=', now()->month)
                                          ->sum('Payments');

        $specBill = ZoneBills::whereYear('Billdate', '=', now()->year)
        ->whereMonth('Billdate', '=', now()->subMonth()->month)
        ->sum('Payment');

        //This has to do with payment
        $total_payments = $ecmi_payment + $ems_payment;
        $today_payment_ecmi = $newpayment->whereDate('TransactionDateTime', now()->toDateString())->sum('Amount');
        $today_payment_ems = ZonePayments::whereDate('PayDate', now()->toDateString())->sum('Payments');

        $selectECMI = ECMIPayment::select("TransactionDateTime", "BUID", "TransactionNo", "Token", 
        "AccountNo", "MeterNo", "Amount",  DB::raw("'prepaid' as CSPClientID"))
        ->orderBy("TransactionDateTime", "desc")->paginate(20);


      $selectEMS = ZonePayments::select("PayDate", "BusinessUnit", "PaymentID", "receiptnumber", 
       "AccountNo", "MeterNo", "Payments",  DB::raw("'postpaid' as PaymentSource"))
       ->orderBy("PayDate", "desc")->paginate(20);
     

       //$bothpayment = $selectECMI->unionAll($selectEMS)->paginate(20);

      // $bothpayment = $selectECMI->unionAll($selectEMS)->paginate(20);
      
       
        $data = [
            'ecmi_payment' => naira_format($today_payment_ecmi),
            'ems_payment' => naira_format($today_payment_ems),
            'total_payments' => naira_format($ecmi_payment + $ems_payment),
            'spec_bills' => naira_format($specBill),
            'payments' => EcmiPaymentResource::collection($selectECMI)->response()->getData(true),
            'postpaid_payment' => ZoneResource::collection($selectEMS)->response()->getData(true),
            'today_payments' => naira_format($today_payment_ecmi + $today_payment_ems),
        ];

        return $this->sendSuccess($data, "Payment Successfully Loaded", Response::HTTP_OK);

    }


    public function getPaymentDetails($account, $Token, $CSPClientID){
        //$formattedAccount = StringHelper::formatAccountNumber($account);
         //   return  $formattedAccount;
        if($CSPClientID == "prepaid"){
           // $payment = ECMIPayment::where('AccountNo', $formattedAccount)->where('Token', $Token)->first();
            $payment = new EcmiPaymentResource(ECMIPayment::where('Token', $Token)->first());
          
        }else {
            //$payment = ZonePayment::where('AccountNo', $account)->where('receiptnumber', $Token)->first();
            $payment = new ZoneResource(ZonePayments::where('receiptnumber', $Token)->first());
        }

        return $this->sendSuccess($payment, "Payment Successfully Loaded", Response::HTTP_OK);
    }


}
