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
        Schema::table('bulkcaad', function (Blueprint $table) {
            $table->string('batch_file_name')->default("0");
            $table->string('batch_status')->default("0");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bulkcaad', function (Blueprint $table) {
            $table->dropColumn('batch_file_name');
            $table->dropColumn('batch_status');
        });
    }
};
