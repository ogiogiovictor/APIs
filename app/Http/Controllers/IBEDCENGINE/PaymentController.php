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
        
        //ECMI Payment For the Current Month
        $ecmi_payment = $newpayment->whereYear('TransactionDateTime', '=', now()->year)
                                          ->whereMonth('TransactionDateTime', '=', now()->month)
                                          ->sum('Amount');

        //EMS Payment for the CurrentMonth
        $ems_payment = ZonePayments::whereYear('PayDate', '=', now()->year)
                                          ->whereMonth('PayDate', '=', now()->month)
                                          ->sum('Payments');

                                    
        // Payment from Spectrum Bill now()->subMonth()->month
        $specBill = ZoneBills::whereYear('Billdate', '=', now()->year)
        ->whereMonth('Billdate', '=', now()->month)
        ->sum('Payment');

        $specBillLastMonth = ZoneBills::whereYear('Billdate', '=', now()->year)
        ->whereMonth('Billdate', '=', now()->subMonth()->month)
        ->sum('Payment');

        //Total Payment for Both ECMI and EMS
        $total_payments = $ecmi_payment + $ems_payment;

        //Today's payment
        $today_payment_ecmi = $newpayment->whereDate('TransactionDateTime', now()->toDateString())->sum('Amount');
        $today_payment_ems = ZonePayments::whereDate('PayDate', now()->toDateString())->sum('Payments');


        //ECMI PAYMENT LIST PAGINATED
        $selectECMI = ECMIPayment::select("TransactionDateTime", "BUID", "TransactionNo", "Token", 
        "AccountNo", "MeterNo", "Amount",  DB::raw("'prepaid' as CSPClientID"))
        ->orderBy("TransactionDateTime", "desc")->paginate(20);

        // EMS PAYMENT LIST PAGINATED
        $selectEMS = ZonePayments::select("PayDate", "BusinessUnit", "PaymentID", "receiptnumber", 
       "AccountNo", "MeterNo", "Payments",  DB::raw("'postpaid' as PaymentSource"))
       ->orderBy("PayDate", "desc")->paginate(20);
     

       //$bothpayment = $selectECMI->unionAll($selectEMS)->paginate(20);

      // $bothpayment = $selectECMI->unionAll($selectEMS)->paginate(20);
      
       
        $data = [
            'ecmi_payment' => naira_format($ecmi_payment),
            'ems_payment' => naira_format($ems_payment),
            'total_payments' => naira_format($ecmi_payment + $ems_payment),
            'spec_bills' => naira_format($specBill),
            'spec_bill_lastMonth' => naira_format($specBillLastMonth),
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
