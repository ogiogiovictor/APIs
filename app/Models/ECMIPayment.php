<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class ECMIPayment extends Model
{
    use HasFactory;

    protected $table = "MAIN_WAREHOUSE_STAGGING.dbo.ecmi_transactions";

    protected $connection = 'stagging';

    public $timestamps = false;

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
