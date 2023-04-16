<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneBills extends Model
{
    use HasFactory;

    use HasFactory;

    protected $table = "MAIN_WAREHOUSE_STAGGING.dbo.Spectrumbill";

    protected $connection = 'stagging';

    public $timestamps = false;

     /**
     * Get the customer that owns this bill.
     */
    public function customer()
    {
        return $this->belongsTo(DimensionCustomer::class, 'AccountNo');
    }

}
