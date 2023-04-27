<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpsDisconnection extends Model
{
    use HasFactory;


    protected $connection = 'ops';

    protected $table = "OPSMGR.dbo.disconnections";

}
