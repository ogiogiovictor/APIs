<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;




class DimensionCustomer extends Model
{
    use  HasFactory;

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
        return $this->hasMany(ZonePayments::class, 'AccountNo', 'AccountNo');
    }

    public function payments()
    { 
       return $this->hasMany(ZonePayments::class, 'AccountNo', 'AccountNo');
    }

    public function transactions() {
        return $this->hasMany(ECMIPayment::class, 'AccountNo', 'AccountNo');
    }

    public function zoneBills()
    {
        return $this->hasMany(ZoneBills::class, 'AccountNo', 'AccountNo')
            ->select('AccountNo', DB::raw('SUM(CurrentChgTotal) as total_billed'))
            ->groupBy('AccountNo');
    }


    public function getTotalOwingAttribute()
    {
        return $this->zoneBills->sum('CurrentChgTotal') - $this->payments->sum('Payments');
    }

    public function factBill()
    {
        return $this->hasOne(ZoneBills::class, 'AccountNo', 'AccountNo')
            ->where('BillYear', '2023')
            ->where('BillMonth', '6');
    }


   
    
}
