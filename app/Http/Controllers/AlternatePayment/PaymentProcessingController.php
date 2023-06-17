<?php

namespace App\Http\Controllers\AlternatePayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Http\Requests\PaymentRequest;
use App\Models\TestModel;

class PaymentProcessingController extends Controller
{
    public function makePayment(PaymentRequest $request){

        if($request->account_type == "Postpaid"){

            TestModel::create([

            ]);

        }

    }
}
