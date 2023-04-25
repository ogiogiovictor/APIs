<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZonePayments extends Model
{
    use HasFactory;

    protected $table = "EMS_ZONE.dbo.Payments";

    protected $connection = 'zone_connection';

    public $timestamps = false;

    public function customer()
    {
        return $this->belongsTo(DimensionCustomer::class, 'AccountNo', 'AccountNo');
    }

}
