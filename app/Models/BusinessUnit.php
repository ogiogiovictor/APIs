<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessUnit extends Model
{
    use HasFactory;

    protected $connection = 'main_warehouse';

    protected $primaryKey = 'rowguid';
    protected $table = "MAIN_WAREHOUSE.dbo.ems_business_unit";

    public $timestamps = false;

    protected $fillable = [
        'BUID', 'ZoneID', 'Name', 'Address', 'State', 'ContactPerson', 'Telephone',
        'Mobile', 'EMail', 'Website', 'bankcode',
        'refcode', 'bucode'
    ];

}
