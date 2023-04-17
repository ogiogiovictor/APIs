<?php

namespace App\Models\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsmsMeterDetails extends Model
{
    use HasFactory;

    protected $table = "msms_meterdetails_tbl";
    protected $primaryKey = 'id';

    public function meter()
    {
        return $this->belongsTo(MsmsMeters::class, 'id', 'meterid');
    }
}
