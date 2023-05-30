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
        Schema::create('kct_generate', function (Blueprint $table) {
            $table->id();
            $table->string('kct_code')->nullable();
            $table->string('meter_number')->nullable();
            $table->string('account_number')->nullable();
            $table->enum('status', ['on', 'off'])->default('off');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kct_generate');
    }
};
