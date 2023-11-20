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
        Schema::create('crmdcustomers_files', function (Blueprint $table) {
            $table->id();
            $table->string('crmd_id');
            $table->string('file_name')->default("null");
            $table->string('file_type')->default("null");
            $table->string('file_path')->default("null");
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crmdcustomers_files');
      
    }
};
