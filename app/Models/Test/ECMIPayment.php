<?php

namespace App\Models\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ECMIPayment extends Model
{
    use HasFactory;

    use HasFactory;

    protected $primaryKey = 'AccountNo';
    protected $table = "transactions";

    public $timestamps = false;

    public function customer()
    {
        return $this->belongsTo(DimensionCustomer::class, 'AccountNo', 'AccountNo');
    }
}
