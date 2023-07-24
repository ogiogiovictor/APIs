<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessCAAD extends Model
{
    use HasFactory;

    protected $table = "process_caad";

    protected $fillable = [
        'accountNo', 'phoneNo', 'surname', 'lastname', 'othername', 'service_center', 'meterno',
        'accountType', 'transtype', 'meter_reading', 'transaction_type', 'effective_date', 'amount',
        'remarks', 'file_upload_id', 'batch_type', 'batch_id', 'status', 'region', 'business_hub',
        'created_by', 'district_accountant', 'business_hub_manager', 'audit', 'regional_manager',
        'hcs', 'cco', 'md'
    ];


    public function fileUpload()
    {
        return $this->hasMany(FileCAAD::class, 'process_caad_id');
    }

   public function CaadComment(){
        return $this->hasMany(CAADCommentApproval::class, 'process_caad_id');
   }
}
