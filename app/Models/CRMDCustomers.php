<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CRMDCustomers extends Model
{
    use HasFactory;

    protected $table = "crmdcustomers";

    protected $fillable = [
        'DateAdded', 'AccountNo', 'MeterNo', 'AcountType', 'Old_FullName', 'New_FullName', 'Address', 
        'DistributionID', 'approval_type', 'confirmed_by', 'approved_by', 'sync', 'new_firstname', 'new_surname', 'new_address', 'mobile', 'new_mobile',
        'region', 'hub', 'service_center', 'userid', 'old_mobile', 'tarriffcode', 'new_tarriff_code', 'email', 'new_email', 'userid'
    ];


    public function getHistory() {
        return $this->hasMany(CRMDHistory::class, "crmd_id", "id");
    }

    public function getFiles()
    {
        return $this->hasMany(CRMDFileUpload::class, 'crmd_id', 'id');
    }


}
