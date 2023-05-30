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
        $getAccountNo = CRMUser::where('id', $ticket->user_id)->first();
        // Now Get the Customer Information.
         $customer = DimensionCustomer::where('AccountNo', $getAccountNo->accountno)
         ->orWhere('MeterNo', $getAccountNo->accountno)->first();
 
         $data = [
             'ticket' => $ticket,
             'customer' => $customer,
             'totalTicket' => $ticket->count(),
         ];

         return $data;
    }


    public function ticketStats(){

        $tickets = Tickets::paginate(20);
        $closedTicket = Tickets::where('status', 'closed')->count();
        $openTickets = Tickets::where('status', 'open')->count();
        $unassignedTickets = Tickets::where('unassigned', 1)->count();

        $data = [
            'tickets' => $tickets,
            'totalTicket' => $tickets->count(),
            'closedTicket' => $closedTicket,
            'openTicket' => $openTickets,
            'unassigned' => $unassignedTickets,
        ];

        return $data;

    }
}
