<?php

namespace App\Repositories;

use App\Repositories\CaadRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Models\BulkCAAD;
use App\Models\FileCAAD;
use App\Models\ProcessCAAD;

class CaadRepository implements CaadRepositoryInterface
{
  
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }


    public function search(){

       $search_term =  $this->request->caad;

        $caad =  ProcessCAAD::select('*')->where(function ($query) use ($search_term ) {
            //$query->whereNotIn("StatusCode", ["0, I, C, N"]);
           // $query->where('Surname', $search_term);
            $query->where('accountNo', 'like', '%'. $search_term .  '%');
            $query->orWhere('surname', $search_term );
        })->get();  //first();
       // Execute search implementation here
        return $caad;
    }



    

}
