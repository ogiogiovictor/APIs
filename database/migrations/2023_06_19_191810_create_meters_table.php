<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meters', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('region')->nullable();
            $table->string('business_hub')->nullable();
            $table->string('transmission_station')->nullable();
            $table->string('33feederline')->nullable();
            $table->string('injection_substation')->nullable();
            $table->string('address')->nullable();
            $table->string('xformer_name')->nullable();
            $table->string('distribution_xformer')->nullable();
            $table->string('dss_name')->nullable();
            $table->string('voltage_ratio')->nullable();
            $table->string('dss_public_private')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('meter_number')->nullable();
            $table->string('meter_model')->nullable();
            $table->string('meter_rated_capacity')->nullable();
            $table->string('installation_capacity')->nullable();
            $table->string('sim_serial_no')->nullable();
            $table->string('network_provider')->nullable();
            $table->string('vendor')->nullable();
            $table->string('installation_date')->nullable();
            $table->string('remarks')->nullable();
            $table->string('sub_station')->nullable();
            $table->string('feeder_name')->nullable();
            $table->string('feeder_category')->nullable();
            $table->string('feeder_band')->nullable();
            $table->string('feeder_type')->nullable();
            $table->string('meter_make')->nullable();
            $table->string('ct_ratio')->nullable();
            $table->string('pt_ratio')->nullable();
            $table->string('account_number')->nullable();
            $table->string('meter_rating')->nullable();
            $table->string('meter_type')->nullable();
            $table->string('category')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('nature_of_business')->nullable();
            $table->string('tariff ')->nullable();
            $table->string('service_band')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('account_name')->nullable();
            $table->string('contact_person_email')->nullable();
            $table->string('contact_person_address')->nullable();
            $table->string('contact_person_phone')->nullable();
            $table->string('initial_reading')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meters');
    }
};
