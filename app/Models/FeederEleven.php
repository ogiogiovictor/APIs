<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Helpers\AssetHelper;

class FeederEleven extends Model
{
    use HasFactory;


    protected $connection = 'ace_db';
    protected $primaryKey = 'msrepl_tran_version';
    protected $table = "Acedata.dbo.11KV Feeder";


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
      ,'F11kvFeeder_parent'
      ,'F11kvFeeder_Name'
      ,'Feeder_CBSerial'
      ,'F11kvFeeder_CBYearofManufacture'
      ,'F11kvFeeder_CB_Make'
      ,'F11kvFeeder_CB_country_of_Manufacture'
      ,'F11kvFeeder_Relay_Make'
      ,'F11kvFeeder_Relay_Type'
      ,'F11kvFeeder_CTRatio'
      ,'F11kvFeeder_RMUSerial'
      ,'F11kvFeeder_RMUYearofManufacture'
      ,'F11kvFeeder_RMU_Make'
      ,'F11kvFeeder_RMU_country_of_Manufacture'
      ,'F11kvFeeder_RMU_Type'
      ,'F11kvFeeder_Route_Length'
      ,'F11kvFeeder_Conductor_Size'
      ,'F11kvFeeder_Aluminium_Conductor'
      ,'F11kvFeeder_UP_Type'
      ,'F11kvFeeder_UP_Length'
      ,'F11kvFeeder_Manufacture'
      ,'F11kvFeeder_Ratedcurrent'
      ,'F11kvFeeder_Ratedvoltage'
      ,'F11kvFeeder_CB_Type'
      ,'Edited by'
      ,'Edited DateTime'
      ,'AssetName'

    ];


    protected static function boot(){
        //$static = "TMP109070";
         parent::boot();
         //static::saving(fn($data) => $data->Assetid = AssetHelper::stripAll($static));
         static::saving(function ($data){
             $static = "FMP132070";
             $data->Assetid = AssetHelper::stripAll($static);
             $data->left = 0;
             $data->top = 0;
             $data->x_image = 0;
             $data->y_image = 0;
         });
       
     }
 
}
