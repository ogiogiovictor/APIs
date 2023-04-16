<?php

namespace App\Models\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZonePayment extends Model
{
    use HasFactory;

    protected $primaryKey = 'BillID';
    protected $table = "ems_payments";

    public $timestamps = false;

    public function customer()
    {
        return $this->belongsTo(DimensionCustomer::class, 'AccountNo', 'AccountNo');
    }

   
}
