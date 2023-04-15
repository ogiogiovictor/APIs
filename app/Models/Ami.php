<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Ami extends Model
{
    use HasFactory;

    protected $table = "DATA_F_DPS_DAY";

    protected $connection = 'ami';

    public $timestamps = false;

  
}
