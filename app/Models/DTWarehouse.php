<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Helpers\AssetHelper;


class DTWarehouse extends Model
{
    use HasFactory;

    protected $connection = 'main_warehouse';

    protected $primaryKey = 'AssetId';

   // protected $table = "MAIN_WAREHOUSE.dbo.gis_dss";
    protected $table = "MAIN_WAREHOUSE.dbo.all_gis_dss";

    public $timestamps = false;

    protected $fillable = [
        'StaffID', 'assettype', 'left', 'top', 'latitude', 'longtitude', 'naccode', 'x_image', 'y_image', 'Capture DateTime', 'Synced DateTime', 'Queried Date', 'Queried Comment',
        'Queried By', 'Verified', 'Verified DateTime', 'Verified By', 'DSS_11KV_415V_parent', 'DSS_11KV_415V_Owner', 'DSS_11KV_415V_Name', 'DSS_11KV_415V_Address', 'DSS_11KV_415V_Rating',
        'DSS_11KV_415V_Make', 'DSS_11KV_415V_Feederpillarr_Type', 'DSS_11KV_415V_FP_Condition', 'DSS_11KV_415V_FP_Catridge', 'DSS_11KV_415V_HV_Fuse', 'DSS_11KV_415V_HV_Fus_Condition',
        'DSS_11KV_415V_Lightning_Arrester', 'DSS_11KV_415V_Serial_No', 'DSS_11KV_415V_Voltage_Ratio', 'DSS_11KV_415V_Oil_Temp', 'DSS_11KV_415V_Winding_Temp', 'DSS_11KV_415V_Manufacture_Year',
        'DSS_11KV_415V_Installation_Year', 'DSS_11KV_415V_country_of_Manufacture', 'DSS_11KV_415V_Percentage_Loading', 'DSS_11KV_415V_No_Load_Loss', 'DSS_11KV_415V_Load_Loss',
        'DSS_11KV_415V_Impedence', 'DSS_11KV_415V_Upriser_Number', 'DSS_11KV_415V_Oil_Level', 'DSS_11KV_415V_Silica_Condition', 'DSS_11KV_415V_Security', 'DSS_11KV_415V_substation_gravelled',
        'DSS_11KV_415V_substation_vegetation', 'DSS_11KV_415V_Low_Voltage_Cable_Size', 'DSS_11KV_415V_Is_Tranformer_Leaking_Oil', 'DSS_11KV_415V_Number_of_HV_Fuse', 'DSS_11KV_415V_Feederpillarr_Available',
        'DSS_11KV_415V_Placement', 'DSS_11KV_415V_cus_profile', 'DSS_11KV_415V_red_line', 'DSS_11KV_415V_Yellow_line', 'DSS_11KV_415V_Blue_line', 'DSS_11KV_415V_Neutral_line', 'DSS_11KV_415V_percent',
        'DSS_11KV_415V_omage', 'DSS_11KV_415V_mounting', 'DSS_11KV_415V_fp_with_meter', 'DSS_11KV_415V_cooling_type', 'DSS_11KV_415V_terminal_arrangement', 'DSS_11KV_415V_Trenches_available',
        'DSS_11KV_415V_Trenches_with_granite', 'DSS_11KV_415V_fence_type', 'Edited by', 'Edited DateTime', 'AssetName', 'msrepl_tran_version'
    ];

    protected static function boot(){
        //$static = "TMP109070";
         parent::boot();
         //static::saving(fn($data) => $data->Assetid = AssetHelper::stripAll($static));
         static::saving(function ($data){
             $static = "TMP109070";
             $data->Assetid = AssetHelper::stripAll($static);
             $data->DSS_11KV_415V_Oil_Level = 0;
             $data->DSS_11KV_415V_Silica_Condition = 0;
         });
       
     }
 
 
     public function serviceUnitEl(): BelongsTo {
         return $this->belongsTo(ServiceUnit::class, "Name");
     }

    public function getCustomerCount(){
        return $this->hasMany(DimensionCustomer::class, 'DistributionID', 'Assetid');
    }

    public function getCustomersInDT()
    {
        return $this->hasMany(DimensionCustomer::class, 'DistributionID', 'Assetid')
        ->selectRaw('DistributionID, count(*) as customer_count')
        ->groupBy('DistributionID');

        // return $this->hasMany(DimensionCustomer::class, 'DistributionID', 'Assetid')
        // ->selectRaw('count(*) as customer_count')
        // ->groupBy('DistributionID')
        // ->value('customer_count');

    }

    public function customerNew()
    {
        return $this->hasOne(DimensionCustomer::class, 'DistributionID', 'Assetid')
            ->collate('SQL_Latin1_General_CP1_CI_AS');
    }

    public function byregion()
    {
         return $this->hasOne(ServiceUnit::class,  'Biz_Hub', 'DSS_11KV_415V_Owner');
    }



    

}
