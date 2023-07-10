<?php

namespace App\Http\Controllers\Export;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
#use App\Models\Test\DimensionCustomer; //please comment this on live
#use App\Models\Test\DTWarehouse;
use App\Models\DimensionCustomer; 
use App\Models\DTWarehouse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Resources\DTResource;

class ExportController extends Controller
{
    public function exportExcel(Request $request){
       
       // return $request->start_date. ' '. $request->end_date . ' '.$request->account_type  . ' '.$request->business_hub . ' '. $request->Region;
       
        if($request->has('download') && $request->download == "download_customer"){

          
            if ($request->account_type == 'Prepaid') {
                $status = ($request->status == 'A') ? 1 : 0;
            } else {
                $status = $request->status;
            }

            
                $data = DimensionCustomer::whereBetween("SetupDate", [$request->start_date, $request->end_date])->where("AccountType", $request->account_type)
            ->where("BusinessHub", $request->business_hub)->where("Region", $request->region)->where("StatusCode", $status)->get();
            
            

             return $mdata = $this->downloadCustomer($data);


        }else if($request->has('download') && $request->download == "download_by_region"){

            $data = DimensionCustomer::where('Region', $request->mregion)->get();

            return $this->downloadCustomer($data);

        }else if($request->has('download') && $request->download == "download_transformer"){

             //$data = DTWarehouse::withCount('getCustomerCount')->with('byregion')->get();
             $data =  DTResource::collection(DTWarehouse::where('Region', $request->region)->withCount('getCustomerCount')->get());

            return $this->downloadDT($data);

        }
       
    }


    private function downloadDT($data){

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Write CSV headers
            fputcsv($file, ['Assetid', 'assettype', 'Capture_Datetime', 'latitude', 'longitude', 'DSS_11KV_415V_Owner', 'DSS_11KV_415V_Name',
            'DSS_11KV_415V_Address', 'DSS_11KV_415V_Rating', 'hub_name', 'Status']);

            // Write data rows
            foreach ($data as $row) {
                fputcsv($file, [$row->Assetid, $row->assettype, $row->Capture_Datetime, $row->latitude, $row->longitude, $row->DSS_11KV_415V_Owner, $row->DSS_11KV_415V_Name
                , $row->DSS_11KV_415V_Address, $row->DSS_11KV_415V_Rating, $row->hub_name, $row->Status]);
            }

            fclose($file);
        };

         // Set the response headers
         $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export.csv"',
        ];

        return new StreamedResponse($callback, 200, $headers);

    }



    private function downloadCustomer($data){

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Write CSV headers
            fputcsv($file, ['SetupDate', 'BookNo', 'MeterNo', 'AccountNo', 'OldAccountNo', 'TariffID', 'Surname', 'FirstName', 'OtherNames',
            'OldTariffCode', 'TarriffCode', 'AccountType', 'AcctTypeDesc', 'Address', 'State', 'City', 'Mobile', 'Email', 'ArrearsBalance',
            'Region', 'BUID', 'BusinessHub', 'service_center', 'StatusCode', 'IsCAPMI', 'Latitude', 'Longitude', 'DistributionID']);

            // Write data rows
            foreach ($data as $row) {
                fputcsv($file, [$row->SetupDate, $row->BookNo, $row->MeterNo, $row->AccountNo, $row->OldAccountNo, $row->TariffID, $row->Surname, $row->FirstName
                , $row->OtherNames, $row->OldTariffCode, $row->TarriffCode, $row->AccountType, $row->AcctTypeDesc, $row->Address, $row->State, $row->City, $row->Mobile
                , $row->Email, $row->ArrearsBalance, $row->Region, $row->BUID, $row->BusinessHub, $row->service_center, $row->StatusCode, $row->IsCAPMI, $row->Latitude
                , $row->Longitude, $row->DistributionID]);
            }

            fclose($file);
        };

         // Set the response headers
         $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export.csv"',
        ];

        return new StreamedResponse($callback, 200, $headers);


    }
}
