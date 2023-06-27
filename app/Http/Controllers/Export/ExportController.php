<?php

namespace App\Http\Controllers\Export;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DimensionCustomer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function exportExcel(Request $request){

       
       // return $request->start_date. ' '. $request->end_date . ' '.$request->account_type  . ' '.$request->business_hub . ' '. $request->Region;
        if($request->has('download') && $request->download == "download_customer"){

          
            $data = DimensionCustomer::whereBetween("SetupDate", [$request->start_date, $request->end_date])
            ->where("AccountType", $request->account_type)->where("BusinessHub", $request->business_hub)->where("Region", $request->Region)->get();


            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');
    
                // Write CSV headers
                fputcsv($file, ['AccountNo', 'Surname', 'DistributionID']);
    
                // Write data rows
                foreach ($data as $row) {
                    fputcsv($file, [$row->AccountNo, $row->Surname, $row->DistributionID]);
                }
    
                fclose($file);
            };
    
            // Set the response headers
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="export.csv"',
            ];

             // Return the streamed response
             return new StreamedResponse($callback, 200, $headers);

        }
       
    }
}
