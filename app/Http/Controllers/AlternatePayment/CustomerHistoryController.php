<?php

namespace App\Http\Controllers\AlternatePayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ECMIPayment;
use App\Models\ECMIPaymentTransaction;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ZonePayments;
use App\Models\ZonePaymentTransaction;
use App\Helpers\StringHelper;

class CustomerHistoryController extends BaseApiController
{
    public function getCustomerHistory($type, $uniqueno){

        if(!$type || !$uniqueno){
            return $this->sendError("Parameters to process request missing", Response::HTTP_BAD_REQUEST);
        }

        switch ($type) {
            case 'prepaid':
                return $this->getPrepaidHistory($type, $uniqueno);
            case 'postpaid':
                return $this->getPostpaidHistory($type, $uniqueno);
            default:
                throw new \InvalidArgumentException('Invalid Request Type');  
        }
        
    }

    private function getPrepaidHistory($type, $meterno){
        
        $getHistory = ECMIPayment::where('meterno', $meterno)->paginate(20);
        if($getHistory){
            return $this->sendSuccess($getHistory, "Prepaid History Retrieved Successfully", Response::HTTP_OK); 
        }else{
            return $this->sendError("ERROR!", "Prepaid History Retrieved Successfully", Response::HTTP_OK); 
        }
        
    }

    private function getPostpaidHistory($type, $accountNo){

        $formatAccount = StringHelper::formatAccountNumber($accountNo);
        $getHistory = ZonePayments::where('AccountNo', $formatAccount)->paginate(20);
        return $this->sendError($getHistory, "Postpaid History Retrieved Successfully", Response::HTTP_OK);
    }
}
