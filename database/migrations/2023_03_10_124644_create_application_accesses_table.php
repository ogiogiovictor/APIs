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
        Schema::create('application_access', function (Blueprint $table) {
            $table->id();
            $table->string('domain_name')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('app-secret')->nullable();
            $table->string('app-token')->nullable();
            $table->string('App_Name')->nullable();
            $table->enum('status', ['on', 'off'])->default('off');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_accesses');
    }
};
