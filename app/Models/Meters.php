<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meters extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'type',
        'region',
        'business_hub',
        'transmission_station',
        '33feederline',
        'injection_substation',
        'address',
        'xformer_name',
        'distribution_xformer',
        'dss_name',
        'voltage_ratio',
        'dss_public_private',
        'latitude',
        'longitude',
        'meter_number',
        'meter_model',
        'meter_rated_capacity',
        'installation_capacity',
        'sim_serial_no',
        'network_provider',
        'vendor',
        'installation_date',
        'remarks',
        'sub_station',
        'feeder_name',
        'feeder_category',
        'feeder_band',
        'feeder_type',
        'meter_make',
        'ct_ratio',
        'pt_ratio',
        'account_number',
        'meter_rating',
        'meter_type',
        'category',
        'customer_name',
        'phone_number',
        'nature_of_business',
        'tariff ',
        'service_band',
        'contact_person',
        'account_name',
        'contact_person_email',
        'contact_person_address',
        'contact_person_phone',
        'initial_reading',


    ];

}
