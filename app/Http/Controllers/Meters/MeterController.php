<?php

namespace App\Http\Controllers\Meters;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meters;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;

class MeterController extends BaseApiController
{
    public function addMeter(Request $request){

        if($request->type){

            $addMeters = Meters::create([
                'type' => $request->type,
                'region' => isset($request->mdata['region']) ? $request->mdata['region'] : '',
                'business_hub' => isset($request->mdata['business_hub']) ? $request->mdata['business_hub'] : '',
                'transmission_station' => isset($request->mdata['transmission_station']) ? $request->mdata['transmission_station'] : '',
                '33feederline' => isset($request->mdata['33feederline']) ? $request->mdata['33feederline'] : '',
                'injection_substation' => isset($request->mdata['injection_substation']) ? $request->mdata['injection_substation'] : '',
                'address' => isset($request->mdata['address']) ? $request->mdata['address'] : '',
                'xformer_name' => isset($request->mdata['xformer_name']) ? $request->mdata['xformer_name'] :'',
                'distribution_xformer' => isset($request->mdata['distribution_xformer']) ? $request->mdata['distribution_xformer'] : '',
                'dss_name' => isset($request->mdata['dss_name']) ? $request->mdata['dss_name'] : '',
                'voltage_ratio' => isset($request->mdata['voltage_ratio']) ? $request->mdata['voltage_ratio'] : '',
                'dss_public_private' => isset($request->mdata['dss_public_private']) ? $request->mdata['dss_public_private'] : '',
                'latitude' => isset($request->mdata['latitude']) ? $request->mdata['latitude'] : '',
                'longitude' => isset($request->mdata['longitude']) ? $request->mdata['longitude'] : '',
                'meter_number' => isset($request->mdata['meter_number']) ? $request->mdata['meter_number'] : '',
                'meter_model' => isset($request->mdata['meter_model']) ? $request->mdata['meter_model'] : '',
                'meter_rated_capacity' => isset($request->mdata['meter_rated_capacity']) ? $request->mdata['meter_rated_capacity'] : '',
                'installation_capacity' => isset($request->mdata['installation_capacity']) ? $request->mdata['installation_capacity'] : '',
                'sim_serial_no' => isset($request->mdata['sim_serial_no']) ? $request->mdata['sim_serial_no'] : '',
                'network_provider' => isset($request->mdata['network_provider']) ? $request->mdata['network_provider'] : '',
                'vendor' => isset($request->mdata['vendor']) ? $request->mdata['vendor'] : '',
                'installation_date' => isset($request->mdata['installation_date']) ? $request->mdata['installation_date'] : '',
                'remarks' => isset($request->mdata['remarks']) ? $request->mdata['remarks'] : '',
                'sub_station' => isset($request->mdata['sub_station']) ? $request->mdata['sub_station'] : '',
                'feeder_name' => isset($request->mdata['feeder_name']) ? $request->mdata['feeder_name'] : '',
                'feeder_category' => isset($request->mdata['feeder_category']) ? $request->mdata['feeder_category'] :  '',
                'feeder_band' => isset($request->mdata['feeder_band']) ? $request->mdata['feeder_band'] : '',
                'feeder_type' => isset($request->mdata['feeder_type']) ? $request->mdata['feeder_type'] : '',
                'meter_make' => isset($request->mdata['meter_make']) ? $request->mdata['meter_make'] : '',
                'ct_ratio' => isset($request->mdata['ct_ratio']) ? $request->mdata['ct_ratio'] :  '',
                'pt_ratio' => isset($request->mdata['pt_ratio']) ? $request->mdata['pt_ratio'] : '',
                'account_number' => isset($request->mdata['account_number']) ? $request->mdata['account_number'] :'',
                'meter_rating' => isset($request->mdata['meter_rating']) ? $request->mdata['meter_rating'] : '',
                'meter_type' => isset($request->mdata['meter_type']) ? $request->mdata['meter_type'] : '',
                'category' => isset($request->mdata['category']) ? $request->mdata['category'] :'',
                'customer_name' => isset($request->mdata['customer_name']) ? $request->mdata['customer_name'] : '',
                'phone_number' => isset($request->mdata['phone_number']) ? $request->mdata['phone_number'] : '',
                'nature_of_business' => isset($request->mdata['nature_of_business']) ?? '',
                'tariff ' => isset($request->mdata['tariff']) ? $request->mdata['tariff'] : '',
                'service_band' => isset($request->mdata['service_band']) ? $request->mdata['service_band'] : '',
                'contact_person' => isset($request->mdata['contact_person']) ? $request->mdata['contact_person'] : '',
                'account_name' => isset($request->mdata['account_name']) ? $request->mdata['account_name'] : '',
                'contact_person_email' => isset($request->mdata['contact_person_email']) ? $request->mdata['contact_person_email'] :  '',
                'contact_person_address' => isset($request->mdata['contact_person_address']) ? $request->mdata['contact_person_address'] : '',
                'contact_person_phone' => isset($request->mdata['contact_person_phone']) ? $request->mdata['contact_person_phone'] : '',
                'initial_reading' => isset($request->mdata['initial_reading']) ? $request->mdata['initial_reading'] : '',

            ]);

            return $this->sendSuccess($addMeters, "Successfully", Response::HTTP_OK);

        }
            
        return $this->sendError("Error", "No Result Found", Response::HTTP_BAD_REQUEST);
        

    }




    public function getMeter(){
        $getMeter = Meters::orderby('created_at', 'desc')->paginate(30);
        return $this->sendSuccess($getMeter, "All Meters", Response::HTTP_OK);
    }


    public function getCustomerRegion($region)
    {
        $region = DimensionCustomer::where('Region', $region)->paginate(40);
        $allCustomer = CustomerResource::collection($region)->response()->getData(true);
        return $this->sendSuccess($allCustomer, "Successfully", Response::HTTP_OK);
    }






}
