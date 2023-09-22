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
        Schema::table('crmdcustomers', function (Blueprint $table) {
            $table->string('region')->default("null");
            $table->string('hub')->default("null");
            $table->string('service_center')->default("null");
            $table->string('userid')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crmdcustomers', function (Blueprint $table) {
            $table->dropColumn('region');
            $table->dropColumn('hub');
            $table->dropColumn('service_center');
            $table->dropColumn('userid');
        });
    }
};
