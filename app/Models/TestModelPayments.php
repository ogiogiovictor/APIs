<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestModelPayments extends Model
{
    use HasFactory;

    protected $table = "EMS_OYO.dbo.PaymentTransaction";

    protected $connection = 'test_environment';

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
