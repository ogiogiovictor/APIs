<?php

namespace App\Models\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DTWarehouse extends Model
{
    use HasFactory;
    protected $table = "gis_dss";
    protected $primaryKey = "msrepl_tran_version";

    public $timestamps = false;

    public function getCustomerCount(){
        return $this->hasMany(DimensionCustomer::class, 'DistributionID', 'Assetid');
    }


    public function byregion()
    {
         return $this->hasOne(ServiceUnit::class,  'Biz_Hub', 'DSS_11KV_415V_Owner');
    }

  

}
