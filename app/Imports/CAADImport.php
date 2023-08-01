<?php

namespace App\Imports;

use App\Models\ProcessCAAD;
use App\Models\BulkCAAD;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class CAADImport implements ToModel, WithHeadingRow
{

    private $bulkCAAD;
    
    public function __construct($bulkCAAD)
    {
        $this->bulkCAAD = $bulkCAAD;
    }
    
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
    
        return new ProcessCAAD([
            'accountNo' => trim($row['accountno']),
            'phoneNo'   => trim($row['phoneno']), 
            'surname'   => trim($row['surname']), 
            'lastname'  => trim($row['lastname']), 
            'othername' => trim($row['othername']), 
            'service_center' => trim($row['service_center']), 
            'meterno'  => trim($row['meterno']), 
            'accountType' => trim($row['accounttype']), 
            'transtype'  => trim($row['transtype']), 
            'meter_reading' => trim($row['meter_reading']), 
            'transaction_type' => trim($row['transaction_type']), 
            'effective_date' => trim($row['effective_date']), 
            'amount' => trim($row['amount']), 
            'remarks' => trim($row['remarks']), 
            'batch_type' => "single", 
            'file_upload_id' => $this->bulkCAAD->batch_file_name, 
            'batch_id' => $this->bulkCAAD->id,
            'region'  => $this->bulkCAAD->region,
            'business_hub' => $this->bulkCAAD->business_hub,
            'created_by' => Auth::user()->id,
            'status' => 0
        ]);
    }
}


