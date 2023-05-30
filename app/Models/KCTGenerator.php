<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KCTGenerator extends Model
{
    use HasFactory;

    protected $table = "kct_generate";

    protected $fillable = [
        'kct_code',
        'meter_number',
        'account_number',
        'status',
    ];
}
