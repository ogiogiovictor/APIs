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
        Schema::create('crmdcustomers', function (Blueprint $table) {
            $table->id();
            $table->string('DateAdded');
            $table->string('AccountNo');
            $table->string('MeterNo')->nullable();
            $table->string('AcountType');
            $table->string('Old_FullName');
            $table->string('New_FullName');
            $table->string('Address')->nullable();
            $table->string('DistributionID')->nullable();
            $table->string('approval_type')->default('0');
            $table->string('confirmed_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('sync')->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crmdcustomers');
    }
};
