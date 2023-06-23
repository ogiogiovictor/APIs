<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneECMI extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.Customers";

    protected $connection = 'ecmi_prod';

    public $timestamps = false;

    

    public function nonSTSCustomers(){
        return $this->whereRaw('LEN(MeterNo) >= 15')->paginate(100);
       
    }
}
