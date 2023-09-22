<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZonePayments extends Model
{
    use HasFactory;

    protected $table = "EMS_ZONE.dbo.Payments";

    protected $connection = 'zone_connection';

    public $timestamps = false;

    public function customer()
    {
        return $this->belongsTo(DimensionCustomer::class, 'AccountNo', 'AccountNo');
    }

    protected $fillable = [
        'PaymentID',
        'BillID',
        'PaymentTransactionId',
        'receiptnumber',
        'PaymentSource',
        'MeterNo',
        'AccountNo',
        'PayDate',
        'PayMonth',
        'PayYear',
        'OperatorID',
        'TotalDue',
        'Payments',
        'Balance',
        'Processed',
        'ProcessedDate',
        'BusinessUnit',
        'Reconciled',
        'ReconciledBy',
        'ReversedBy',
        'BatchUniqueID',
        'rowguid',
        'DateEngtered',
        'CustomerID',
    ];


    public function checkForDuplicates()
    {
        $duplicates = DB::table('Payments')
            ->select('receiptnumber', DB::raw('COUNT(*) AS DuplicateCount'))
            ->where('PayYear', '2023')
            ->groupBy('receiptnumber')
            ->having('DuplicateCount', '>', 1)
            ->get();

        return $duplicates;
    }
     
    

}
