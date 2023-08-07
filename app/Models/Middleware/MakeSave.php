<?php

namespace App\Models\Middleware;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MakeSave extends Model
{
    use HasFactory;

    protected $table = "make_saves";

    protected $fillable = [
        'uniqueID',
        'amount',
        'unit',
        'transaction_ref',
        'account_no',
        'meter_no',
        'name',
        'ecmi_ref'
    ];

}
