<?php

namespace App\Imports;

use App\Models\ProcessCAAD;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CAADImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
      return $row['accountno'];
        return new ProcessCAAD([
        // 'accountNo'       => $row[0],
        // 'phoneNo'         => $row[1], 
        // 'surname'         => $row[2], 
        // 'lastname'        => $row[3], 
        // 'othername'       => $row[4], 
        // 'service_center'  => $row[5], 
        // 'meterno'         => $row[6], 
        // 'accountType'     => $row[7], 
        // 'transtype'       => $row[8], 
        // 'meter_reading'   => $row[9], 
        // 'transaction_type'=> $row[10], 
        // 'effective_date'  => $row[11], 
        // 'amount'          => $row[12], 
        // 'remarks'         => $row[13], 
        // 'file_upload_id'  => $row[14], 

            'accountNo'  => $row['accountNo'],
            'phoneNo'    => $row['phoneNo'], 
            'surname'    => $row['surname'], 
            'lastname'    => $row['lastname'], 
            'othername'    => $row['othername'], 
            'service_center'  => $row['service_center'], 
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
