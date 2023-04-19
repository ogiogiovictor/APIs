<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DimensionCustomer extends Model
{
    use HasFactory;

    protected $connection = 'main_warehouse';

    protected $primaryKey = 'CustomerSK';

    protected $table = "MAIN_WAREHOUSE.dbo.Dimension_customers";

    public $timestamps = false;

    protected $fillable = [
        'BookNo', 'MeterNo', 'AccountNo', 'Surname', 'Firstname'
    ];

    public function bills()
    {
        return $this->hasMany(ZoneBills::class, 'AccountNo', 'AccountNo');
    }

    public function postpaid()
    {
        return $this->hasMany(ZonePayment::class, 'AccountNo', 'AccountNo');
    }

    public function payments()
    { 
       return $this->hasMany(ZonePayment::class, 'AccountNo', 'AccountNo');
    }

    public function transactions() {
        return $this->hasMany(ECMIPayment::class, 'AccountNo', 'AccountNo');
    }
    
}
