<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentModel extends Model
{
    use HasFactory;

    protected $table = "payment_logs";

    protected $fillable = [
        'email',
        'transaction_id',
        'phone',
        'amount',
        'account_type',
        'account_number',
        'meter_no',
        'status',
        'customer_name',
        'date_entered',
        'BUID',
        'provider',
        'providerRef',
        'receiptno',
        'payment_source',
        'Descript',
        'owner',
        'response_status'
    ];

}

