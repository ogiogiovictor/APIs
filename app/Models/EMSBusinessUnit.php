<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EMSBusinessUnit extends Model
{
    use HasFactory;

    protected $table = "EMS_ZONE.dbo.BusinessUnit";

    protected $connection = 'zone_connection';

    public $timestamps = false;

    protected $primaryKey = 'BUID';

    protected $fillable = [
        'BUID', 'ZoneID', 'Name', 'Address', 'State', 'ContactPerson', 'Telephone',
        'Mobile', 'EMail', 'Website', 'bankcode',
        'refcode', 'bucode'
    ];
}
