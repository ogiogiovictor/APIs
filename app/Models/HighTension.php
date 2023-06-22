<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HighTension extends Model
{
    use HasFactory;

    protected $connection = 'main_warehouse';

    //protected $primaryKey = 'CustomerSK';

    protected $table = "MAIN_WAREHOUSE.dbo.gis_hightension";

    public $timestamps = false;

   
}
