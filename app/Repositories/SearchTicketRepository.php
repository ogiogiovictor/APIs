<?php

namespace App\Repositories;

use App\Repositories\SearchRepositoryInterface;
use App\Models\Tickets;



class SearchTicketRepository implements SearchRepositoryInterface
{
  
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }


    public function search(){

       $search_term =  $this->request->Tickets;

        $tickets =  Tickets::select('*')->where(function ($query) use ($search_term ) {
            //$query->whereNotIn("StatusCode", ["0, I, C, N"]);
           // $query->where('Surname', $search_term);
            $query->where('ticket_no', 'like', '%'. $search_term .  '%');
            $query->orWhere('category_name', $search_term );
            $query->orWhere('location_name', $search_term);
        })->get();  //first();
       // Execute search implementation here
        return $tickets;
    }

   

}
