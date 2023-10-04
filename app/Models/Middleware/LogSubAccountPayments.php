<?php

namespace App\Models\Middleware;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogSubAccountPayments extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.Log_SubAccPayment";

    protected $connection = 'ecmi_prod';

    public $timestamps = false;
}
