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


    public function ticketStats($request){
    
        $reqStatus = '';

        if ($request === 'closed') {
            $reqStatus = 'closed';
        } elseif ($request === 'open') {
            $reqStatus = 'open';
        } elseif ($request === 'unassigned') {
            $reqStatus = 'unassigned';
        }

        //$tickets = Tickets::where("status", $reqStatus)->orderby('created_at', 'desc')->paginate(20);
        $tickets =  $tickets = Tickets::when($reqStatus, function ($query) use ($reqStatus) {
            return $query->where('status', $reqStatus);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        $closedTicket = Tickets::where('status', 'closed')->count();
        $openTickets = Tickets::where('status', 'open')->count();
        $unassignedTickets = Tickets::where('unassigned', 1)->count();
        $totalTickets = Tickets::count();

        $data = [
            'tickets' => $tickets,
            'totalTicket' => number_format($totalTickets),
            'closedTicket' => number_format($closedTicket),
            'openTicket' => number_format($openTickets),
            'unassigned' => $unassignedTickets,
        ];

        return $data;

    }
}
