<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ECMIBusinessUnit extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.BusinessUnit";

    protected $connection = 'ecmi_prod';

    public $timestamps = false;

    protected $primaryKey = 'BUID';

    protected $fillable = [
        'BUID', 'ZoneID', 'Name', 'Address', 'State', 'ContactPerson', 'Telephone',
        'Mobile', 'EMail', 'Website', 'bucode',
        'RegionID', 
    ];
}
