<?php

namespace App\Models\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneBills extends Model
{
    use HasFactory;

    protected $primaryKey = 'BillID';
    protected $table = "spectrumbill";

    public $timestamps = false;

    /**
     * Get the customer that owns this bill.
     */
    public function customer()
    {
        return $this->belongsTo(DimensionCustomer::class, 'AccountNo', 'AccountNo');
    }
}
