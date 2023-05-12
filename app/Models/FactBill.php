<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactBill extends Model
{
    use HasFactory;

    protected $connection = 'main_warehouse';

    protected $primaryKey = 'CustomerSK';

    protected $table = "MAIN_WAREHOUSE.dbo.FactBill";

    public $timestamps = false;

    public function customer()
    {
        
        return $this->belongsTo(DimensionCustomer::class, 'AccountNo');
    }
}
