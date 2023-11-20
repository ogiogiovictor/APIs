<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CRMDFileUpload extends Model
{
    use HasFactory;

    protected $table = "crmdcustomers_files";

    protected $fillable = [
        'crmd_id', 'file_name', 'file_type', 'file_path', 'document_type', 'account_no'
    ];

    public function getFileCRMD()
    {
        return $this->belongsTo(CRMDCustomers::class, 'crmd_id', 'id');
    }
}
