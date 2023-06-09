<?php

namespace App\Repositories;

use App\Repositories\SearchRepositoryInterface;
use App\Models\DimensionCustomer;
use App\Models\DTWarehouse;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ECMIPayment;
use App\Models\ZonePayments;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\EcmiPaymentResource;
use App\Http\Resources\ZoneResource;



class SearchPaymentRepository implements SearchRepositoryInterface
{
  
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }


    public function search(){

      $search_term =  $this->request->Payment;

     
      $selectECMI = ECMIPayment::select('*')->where(function ($query) use ($search_term ) {
        //$query->whereNotIn("StatusCode", ["0, I, C, N"]);
       // $query->where('Surname', $search_term);
        $query->where('AccountNo', 'like', '%'. $search_term .  '%');
        $query->orWhere('MeterNo', $search_term );
        $query->orWhere('Token', $search_term);
    })->paginate(60); //first();
   // Execute search implementation here

   $selectEMS = ZonePayments::select('*')->where(function ($query) use ($search_term ) {
    //$query->whereNotIn("StatusCode", ["0, I, C, N"]);
   // $query->where('Surname', $search_term);
    $query->where('AccountNo', 'like', '%'. $search_term .  '%');
    $query->orWhere('MeterNo', $search_term );
    $query->orWhere('receiptnumber', $search_term);
})->orderBy("PayDate", "DESC")->paginate(60); //first();
// Execute search implementation here

       
       $selectEMS = ZonePayments::select("PayDate", "BusinessUnit", "PaymentID", "receiptnumber", 
      "AccountNo", "MeterNo", "Payments",  DB::raw("'postpaid' as PaymentSource"))
        ->where("AccountNo", "LIKE", "%{$search_term}%")
        ->orWhere("MeterNo", "LIKE", "%{$search_term}%")
        ->orWhere("receiptnumber", "LIKE", "%{$search_term}%")
      ->orderBy("PayDate", "DESC")->paginate(60);

       $data = [
        //'ecmi_payment' => naira_format($ecmi_payment),
        //'ems_payment' => naira_format($ems_payment),
        //'total_payments' => naira_format($ecmi_payment + $ems_payment),
        //'spec_bills' => naira_format($specBill),
        //'spec_bill_lastMonth' => naira_format($specBillLastMonth),
        //'last_month_prepaid' => naira_format($ecmi_payment_lastMonth),
        'payments' => EcmiPaymentResource::collection($selectECMI)->response()->getData(true),
        'postpaid_payment' => ZoneResource::collection($selectEMS)->response()->getData(true),
        //'today_payments' => naira_format($today_payment_ecmi + $today_payment_ems), 
    ];

    return response()->json($data);

       
    }

   

}
