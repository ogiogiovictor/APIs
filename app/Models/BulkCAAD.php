<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkCAAD extends Model
{
    use HasFactory;

    protected $table = "bulkcaad";

    protected $fillable = [
        'bulk_unique_id', 'batch_name', 'business_hub', 'batch_file_name'
    ];


    public function withmanycaads(){
        return $this->hasMany(ProcessCAAD::class, 'batch_id');
    }

    public function withmayncomments(){
        return $this->hasMany(CAADCommentApproval::class, 'batch_id');
    }
}
