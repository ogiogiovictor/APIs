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

   // protected $primaryKey = 'Token';
   // protected $primaryKey = 'transref';
   protected $primaryKey =  'TransactionNo';
    

    public function nonSTSCustomers(){
        return $this->whereRaw('LEN(MeterNo) >= 15')->orderBy("OpenDate", "desc")->paginate(100);
       
    }

    public function customer()
    {
        return $this->belongsTo(DimensionCustomer::class, 'AccountNo', 'AccountNo');
    }

    public function customerE(): BelongsTo {
        return $this->belongsTo(DimensionCustomer::class, "AccountNo");
    }

    public function paymentCount(){
        return $this->count();
    }

}
