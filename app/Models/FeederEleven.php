<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeederEleven extends Model
{
    use HasFactory;


    protected $primaryKey = 'msrepl_tran_version';
    protected $table = "Acedata.dbo.11KV Feeder";

    public $timestamps = false;
}
