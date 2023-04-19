<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsmsMeters extends Model
{
    use HasFactory;

    protected $connection = 'msms';
    protected $table = "map_meter_allocation_tbl";

    protected $primaryKey = 'customerid';

    public function customer(){
        return $this->belongsTo(MsmsCustomer::class, 'id', 'customerid');
    }

    public function meter_details()
    {
        return $this->hasOne(MsmsMeterDetails::class, 'id', 'meterid');
    }

    
}
