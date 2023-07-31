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
       //echo $row['accountno'];
        //return;
        
        return new ProcessCAAD([
            'accountNo' => $row['accountno'],
            'phoneNo'   => $row['phoneno'], 
            'surname'   => $row['surname'], 
            'lastname'  => $row['lastname'], 
            'othername' => $row['othername'], 
            'service_center' => $row['service_center'], 
            'meterno'  => $row['meterno'], 
            'accountType'    => $row['accounttype'], 
            'transtype'    => $row['transtype'], 
            'meter_reading'    => $row['meter_reading'], 
            'transaction_type'    => $row['transaction_type'], 
            'effective_date'    => $row['effective_date'], 
            'amount'    => $row['amount'], 
            'remarks'    => $row['remarks'], 
            'batch_type'    => "batched", 
            'file_upload_id'    => $this->bulkCAAD->batch_file_name, 
            'batch_id' => $this->bulkCAAD->id,
            'region'    => $this->bulkCAAD->region,
            'business_hub'    => $this->bulkCAAD->business_hub,
            'created_by' => Auth::user()->id,
            'status' => 0
        ]);
    }
}
