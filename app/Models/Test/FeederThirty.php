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
        'F33kv_Feeder_Name',  'latitude',  'longtitude',  'naccode', 'Capture DateTime', 'assettype',
        'F33kv_Regional_Name', 'F33kv_Business_Hub_Name',
        'F33kv_Feeder_Name', 'F33kV_Feeder_Circuit_Breaker_Make', 'F33kV_Feeder_Circuit_Breaker_Type', 'F33kV_Upriser_Cable_Type', 'F33kv_Teeoffs',
        'F33kv_Tee_offs_Coordinate',
        'F33kv_Substations_capacity', 'F33kv_lineload_coordinate', 'F33kv_Conductor_Size',  'F33kv_Aluminium_Conductor',  'F33kv_Commisioning',
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
