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
        'region', 'hub', 'service_center', 'userid'
    ];

}
