<?php

namespace App\Models\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\AssetHelper;

class FeederThirty extends Model
{
    use HasFactory;

    protected $primaryKey = 'msrepl_tran_version';
    protected $table = "33KV feeder";

    public $timestamps = false;

    protected $fillable = [
        'F33kv_Feeder_Name',
        'latitude',
        'longtitude',
        'naccode',
        'Capture DateTime',
        'assettype',
    ];

    protected static function boot(){
        //$static = "TMP109070";
         parent::boot();
         //static::saving(fn($data) => $data->Assetid = AssetHelper::stripAll($static));
         static::saving(function ($data){
             $static = "FMP116070";
             $data->Assetid = AssetHelper::stripAll($static);
         });
       
     }

   

}
