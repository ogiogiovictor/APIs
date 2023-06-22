<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feeders extends Model
{
    use HasFactory;

    protected $connection = 'main_warehouse';

    protected $table = "MAIN_WAREHOUSE.dbo.gis_feeders";

    public $timestamps = false;

}
