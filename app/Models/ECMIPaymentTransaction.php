<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ECMIPaymentTransaction extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.PaymentTransaction";

    protected $connection = 'ecmi_prod';

    public $timestamps = false;
}
