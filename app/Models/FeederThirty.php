<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeederThirty extends Model
{
    use HasFactory;

    protected $primaryKey = 'Assetid';
    protected $table = "Acedata.dbo.33KV Feeder";

    public $timestamps = false;
}
