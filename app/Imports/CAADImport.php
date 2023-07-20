<?php

namespace App\Imports;

use App\Models\ProcessCAAD;
use Maatwebsite\Excel\Concerns\ToModel;

class CAADImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ProcessCAAD([
            'accountNo'  => $row['accountNo'],
            'phoneNo'    => $row['phoneNo'], 
            'surname'    => $row['surname'], 
            'lastname'    => $row['lastname'], 
            'othername'    => $row['othername'], 
            'service_center'    => $row['service_center'], 
            'meterno'    => $row['meterno'], 
            'accountType'    => $row['accountType'], 
            'transtype'    => $row['transtype'], 
            'meter_reading'    => $row['meter_reading'], 
            'transaction_type'    => $row['transaction_type'], 
            'effective_date'    => $row['effective_date'], 
            'amount'    => $row['amount'], 
            'remarks'    => $row['remarks'], 
            'file_upload_id'    => $row['file_upload_id'], 
        ]);
    }
}
