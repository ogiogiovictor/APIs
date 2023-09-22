<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZonePaymentTransaction extends Model
{
    use HasFactory;

    protected $table = "EMS_ZONE.dbo.PaymentTransaction";

    protected $connection = 'zone_connection';

    public $timestamps = false;

    protected $fillable = [
        'transid',
        'transref',
        'enteredby',
        'transdate',
        'transamount',
        'transstatus',
        'accountno',
        'transactionresponsemessage',
        'paymenttype',
        'TransactionBusinessUnit',
    ];


}
