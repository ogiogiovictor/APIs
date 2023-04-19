<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsmsMeterDetails extends Model
{
    use HasFactory;

    protected $connection = 'msms';
    protected $table = "meterdetails_tbl";

    public function meter()
    {
        return $this->belongsTo(MsmsMeters::class, 'id', 'meterid');
    }

}
