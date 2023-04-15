<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EMSPayment extends Model
{
    use HasFactory;

    protected $table = "MAIN_WAREHOUSE_STAGGING.dbo.ems_payments";

    protected $connection = 'stagging';

    public $timestamps = false;

    public function customerM(): BelongsTo {
        return $this->belongsTo(DimensionCustomer::class, "AccountNo");
    }
}
