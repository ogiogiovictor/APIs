<?php

namespace App\Models\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\AssetHelper;

class FeederEleven extends Model
{
    use HasFactory;

    protected $primaryKey = 'msrepl_tran_version';
    protected $table = "11KV feeder";

    public $timestamps = false;

    protected $fillable = [
        'F11kvFeeder_Name',
        'Feeder_CBSerial',
        'F11kvFeeder_CBYearofManufacture',
        'F11kvFeeder_CB_Make',
        'F11kvFeeder_CB_country_of_Manufacture',
        'F11kvFeeder_Relay_Make',
        'F11kvFeeder_Relay_Type',
        'F11kvFeeder_CTRatio',
        'F11kvFeeder_parent',
        'F11kvFeeder_RMUSerial',
        'F11kvFeeder_RMUYearofManufacture',
        'F11kvFeeder_RMU_Make',
        'F11kvFeeder_RMU_country_of_Manufacture',
        'F11kvFeeder_RMU_Type',
        'F11kvFeeder_Route_Length',
        'F11kvFeeder_Conductor_Size',
        'F11kvFeeder_Aluminium_Conductor',
        'F11kvFeeder_UP_Type',
        'F11kvFeeder_UP_Length',
        'F11kvFeeder_Manufacture',
        'F11kvFeeder_Ratedcurrent',
        'F11kvFeeder_Ratedvoltage',
        'F11kvFeeder_CB_Type',
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
             $static = "FMP109070";
             $data->Assetid = AssetHelper::stripAll($static);
         });
       
     }
}
