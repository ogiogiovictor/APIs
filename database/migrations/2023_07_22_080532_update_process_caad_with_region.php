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
        Schema::table('process_caad', function (Blueprint $table) {
            $table->string('region')->default("null");
            $table->string('business_hub')->default("null");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('process_caad', function (Blueprint $table) {
            $table->dropColumn('region');
            $table->dropColumn('business_hub');
        });
    }
};