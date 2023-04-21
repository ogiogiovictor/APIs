<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tickets;
use App\Http\Requests\TicketRequest;
use App\Services\TicketService;

class TicketController extends Controller
{
    public function index(){
        $ticketData = (new TicketService)->ticketStats();

        if($ticketData){
            return $this->sendSuccess($ticketData, "Ticket Successfully  Loaded", Response::HTTP_OK);
        }else {
            return $this->sendError("No Data", "No data Found" , Response::HTTP_NO_CONTENT);
        }
    }



    public function show(TicketRequest $request){

        $ticket = Tickets::where('ticket_no', $request->ticketid)->first();

        $custTicketData = (new ticketService())->getTicket($ticket);

        if($custTicketData){
            return $this->sendSuccess($custTicketData, "Customer 360 Loaded", Response::HTTP_OK);
        }else {
            return $this->sendError("No Data", "No data Found" , Response::HTTP_NO_CONTENT);
        } 

    }
}
