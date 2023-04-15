<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tickets;

class TicketController extends Controller
{
    public function index(){
        return Tickets::paginate(20);
    }
}
