<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneBills extends Model
{
    use HasFactory;

    use HasFactory;

    protected $table = "EMS_ZONE.dbo.SpectrumBill";

    protected $connection = 'zone_connection';

    public $timestamps = false;

     /**
     * Get the customer that owns this bill.
     */
    public function customer()
    {
        
        return $this->belongsTo(DimensionCustomer::class, 'AccountNo');
    }

}
