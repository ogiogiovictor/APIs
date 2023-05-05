<?php

namespace App\Helpers;

class AssetHelper
{
  

    public static function stripAll($data = null){
        if($data){
            return preg_replace('/[\W]+/', '', $data.self::getNewDate());
        }

        return null;
    }

    public static function getNewDate(){
    
      $current_date = GETDATE();

      return $current_date['year'].$current_date['yday'].$current_date['mon'].$current_date['wday'].$current_date['mday'].$current_date['hours'].$current_date['minutes'].$current_date['seconds'];

    }


    public static function dataRequest($request){
        $date = date('Y-m-d H:i:s');

        $assetData = $request->all();
        $assetData['left'] = 0;
        $assetData['top'] = 0;
        $assetData['x_image'] = 0;
        $assetData['y_image'] = 0;
        $assetData['Capture DateTime'] = $date;
       // $assetData['AssetName'] = $request['DSS_11KV_415V_Name'];
        

        return $assetData;
    }
}