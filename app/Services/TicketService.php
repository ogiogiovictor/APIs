<?php

namespace App\Services;
use App\Models\DimensionCustomer;
use App\Models\ZoneCustomer;
use App\Models\ECMICustomer;
use DB;
use App\Models\Tickets;
use App\Models\CRMUser;




class TicketService
{
   

    public function getTicket($ticket)
    {
        $getAccountNo = CRMUsers::where('id', $ticket->user_id)->first();
        // Now Get the Customer Information.
         $customer = DimensionCustomer::where('AccountNo', $getAccountNo->accountno)
         ->orWhere('MeterNo', $getAccountNo->accountno)->first();
 
         $data = [
             'ticket' => $ticket,
             'customer' => $customer,
         ];

         return $data;
    }
}
