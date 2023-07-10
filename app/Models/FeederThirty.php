<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Helpers\AssetHelper;

class FeederThirty extends Model
{
    use HasFactory;

    protected $primaryKey = 'msrepl_tran_version';
    protected $table = "Acedata.dbo.33KV Feeder";

    public $timestamps = false;

    protected $fillable = [ 
      'Assetid'
      ,'StaffID'
      ,'assettype'
      ,'left'
      ,'top'
      ,'latitude'
      ,'longtitude'
      ,'naccode'
      ,'x_image'
      ,'y_image'
      ,'Capture DateTime'
      ,'Synced DateTime'
      ,'Queried'
      ,'Queried Date'
      ,'Queried Comment'
      ,'Queried By'
      ,'Verified'
      ,'Verified DateTime'
      ,'Verified By'
      ,'F33kv_Regional_Name'
      ,'F33kv_Business_Hub_Name'
      ,'F33kv_Feeder_parent'
      ,'F33kv_Feeder_Name'
      ,'F33kV_Feeder_Circuit_Breaker_Make'
      ,'F33kV_Feeder_Circuit_Breaker_Type'
      ,'F33kV_Upriser_Cable_Type'
      ,'F33kv_Teeoffs'
      ,'F33kv_Tee_offs_Coordinate'
      ,'F33kv_Substations_capacity'
      ,'F33kv_lineload_coordinate'
      ,'F33kv_Conductor_Size'
      ,'F33kv_Aluminium_Conductor'
      ,'F33kv_Commisioning'
      ,'Edited by'
      ,'Edited DateTime'
      ,'AssetName'


    ];


    protected static function boot(){
        //$static = "TMP109070";
         parent::boot();
         //static::saving(fn($data) => $data->Assetid = AssetHelper::stripAll($static));
         static::saving(function ($data){
             $static = "FMP152070";
             $data->Assetid = AssetHelper::stripAll($static);
             $data->left = 0;
             $data->top = 0;
             $data->x_image = 0;
             $data->y_image = 0;
         });
       
     }
}
