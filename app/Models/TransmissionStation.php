<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransmissionStation extends Model
{
    use HasFactory;

    protected $connection = 'ace_db';

    protected $primaryKey = 'msrepl_tran_version';
    protected $table = "Acedata.dbo.132kV Transmission Substation";

    public $timestamps = false;
}
