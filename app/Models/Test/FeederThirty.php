<?php

namespace App\Models\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeederThirty extends Model
{
    use HasFactory;

    protected $primaryKey = 'msrepl_tran_version';
    protected $table = "33KV Feeder";

    public $timestamps = false;
}
