<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessCAAD extends Model
{
    use HasFactory;

    protected $table = "process_caad";

    protected $fillable = [
        'accountNo', 'phoneNo', 'surname', 'lastname', 'othername', 'service_center', 'meterno',
        'accountType', 'transtype', 'meter_reading', 'transaction_type', 'effective_date', 'amount',
        'remarks', 'file_upload_id'
    ];
}