<?php

namespace App\Imports;

use App\Models\ProcessCAAD;
use App\Models\BulkCAAD;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class CAADImport implements ToModel, WithHeadingRow
{

    private $batch_id;
    
    public function __construct($batch_id)
    {
        $this->batch_id = $batch_id;
    }
    
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        return new ProcessCAAD([
            'accountNo'  => $row['accountno'],
            'phoneNo'    => $row['phoneno'], 
            'surname'    => $row['surname'], 
            'lastname'    => $row['lastname'], 
            'othername'    => $row['othername'], 
            'service_center'    => $row['service_center'], 
            'meterno'    => $row['meterno'], 
            'accountType'    => $row['accounttype'], 
            'transtype'    => $row['transtype'], 
            'meter_reading'    => $row['meter_reading'], 
            'transaction_type'    => $row['transaction_type'], 
            'effective_date'    => $row['effective_date'], 
            'amount'    => $row['amount'], 
            'remarks'    => $row['remarks'], 
            'file_upload_id'    => $row['file_upload_id'], 
            'batch_type'    => "batched", 
            'batch_id' => $this->batch_id
        ]);
    }
}
