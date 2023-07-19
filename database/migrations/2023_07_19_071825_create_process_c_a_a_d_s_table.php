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
        Schema::create('process_caad', function (Blueprint $table) {
            $table->id();
            $table->string('accountNo');
            $table->string('phoneNo');
            $table->string('surname');
            $table->string('lastname');
            $table->string('othername');
            $table->string('service_center');
            $table->string('meterno');
            $table->string('accountType');
            $table->string('transtype');
            $table->string('meter_reading');
            $table->string('transaction_type');
            $table->string('effective_date');
            $table->string('amount');
            $table->string('remarks');
            $table->string('file_upload_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_caad');
    }
};
