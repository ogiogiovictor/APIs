<?php

namespace App\Models\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeederEleven extends Model
{
    use HasFactory;

    protected $primaryKey = 'msrepl_tran_version';
    protected $table = "11KV Feeder";

    public $timestamps = false;
}
