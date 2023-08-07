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
        Schema::create('make_saves', function (Blueprint $table) {
            $table->id();
            $table->string('uniqueID')->default(0);
            $table->string('amount')->default(0);
            $table->string('unit')->default(0);
            $table->string('transaction_ref')->default(0);
            $table->string('account_no')->default(0);
            $table->string('meter_no')->default(0);
            $table->string('name')->default(0);
            $table->string('ecmi_ref')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('make_saves');
    }
};
