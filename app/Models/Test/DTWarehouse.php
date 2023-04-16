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
}
