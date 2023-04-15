<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceUnit extends Model
{
    use HasFactory;

    protected $connection = 'ace_db';

    //protected $primaryKey = 'msrepl_tran_Version';
    //protected $primaryKey = 'Id';
    //protected $table = "Acedata.dbo.ServiceUnits";

    protected $primaryKey = 'Id';
    protected $table = "Acedata.dbo.ServiceUnits";

    public $timestamps = false;

    protected $fillable = [ 'Name', 'Biz_Hub', 'Region' ];

    // public function serviceUnitEl(): HasMany {
    //     return $this->belongsTo(DTEleven::class, "DSS_11KV_415V_owner");
    // }

    // public function serviceUnitTh(): HasMany {
    //     return $this->belongsTo(DTThirty::class, "DSS_33KV_415V_owner");
    // }
    

}
