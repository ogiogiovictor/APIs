<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ECMITransactions extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.Transactions";

    protected $connection = 'ecmi_prod';

    public $timestamps = false;

    protected $primaryKey = 'Token';
    

    public function nonSTSCustomers(){
        return $this->whereRaw('LEN(MeterNo) >= 15')->orderBy("OpenDate", "desc")->paginate(100);
       
    }
}
