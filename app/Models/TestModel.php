<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasFactory;

    protected $table = "EMS_OYO.dbo.Payments";

    protected $connection = 'test_environment';

    public $timestamps = false;


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

     
}
