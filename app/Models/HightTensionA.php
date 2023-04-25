<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HightTensionA extends Model
{
    use HasFactory;

    protected $connection = 'ace_db';

    protected $primaryKey = 'msrepl_tran_version';
    protected $table = "Acedata.dbo.High Tension Pole 11KV";

    public $timestamps = false;
}
