<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CRMDHistory extends Model
{
    use HasFactory;

    protected $table = "crmd_history";

    protected $fillable = [
        'user_id', 'crmd_id', 'status', 'approval', 'comment'
    ];

}
