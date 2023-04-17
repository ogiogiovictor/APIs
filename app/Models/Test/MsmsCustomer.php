<?php

namespace App\Models\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsmsCustomer extends Model
{
    use HasFactory;

    protected $table = "msms_customers";
    protected $primaryKey = 'id';

    public function customer_meters(){
        return $this->hasOne(MsmsMeters::class, 'customerid', 'id');
    }

    public function meter_details()
    {
        return $this->hasManyThrough(
            MsmsMeterDetails::class,
            MsmsMeters::class,
            'customerid', // Foreign key on the MsmsMeters table
            'id', // Foreign key on the MeterDetails table  //meter_id
            'id', // Local key on the MsmsCustomer table
            'meterid' // Local key on the MsmsMeters table
        );
    }

}
