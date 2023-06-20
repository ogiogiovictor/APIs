<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSource extends Model
{
    use HasFactory;

    protected $table = "EMS_ZONE.dbo.PaymentSource";

    protected $connection = 'zone_connection';

    public $timestamps = false;
}
