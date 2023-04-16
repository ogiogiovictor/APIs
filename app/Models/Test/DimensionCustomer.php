<?php

namespace App\Models\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DimensionCustomer extends Model
{
    use HasFactory;

    protected $table = "customers";
    protected $primaryKey = "CustomerSK";

    public $timestamps = false;

      public function bills()
    {
        //return $this->hasMany(ZoneBills::class, "AccountNo");
        return $this->hasMany(ZoneBills::class, 'AccountNo', 'AccountNo');
    }

    public function postpaid()
    {
        return $this->hasMany(ZonePayment::class, 'AccountNo', 'AccountNo');
    }

    public function payments()
    {
        
       return $this->hasMany(ZonePayment::class, 'AccountNo', 'AccountNo');
        
    }

    public function transactions() {
        return $this->hasMany(ECMIPayment::class, 'AccountNo', 'AccountNo');
    }

   
}
