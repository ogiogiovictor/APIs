<?php

namespace App\Models\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ECMIPayment extends Model
{
    use HasFactory;

    use HasFactory;

    protected $primaryKey = 'Token';
    protected $table = "transactions";

    public $timestamps = false;

    public $incrementing = false; // Specify that the primary key is not an auto-incrementing integer

    public function customer()
    {
        return $this->belongsTo(DimensionCustomer::class, 'AccountNo', 'AccountNo');
    }

    public function paymentCount(){
        return $this->count();
    }
}
