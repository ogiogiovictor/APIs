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
            $table->string('created_by')->default(0);
            $table->string('district_accountant')->default(0);
            $table->string('business_hub_manager')->default(0);
            $table->string('audit')->default(0);
            $table->string('regional_manager')->default(0);
            $table->string('hcs')->default(0);
            $table->string('cco')->default(0);
            $table->string('md')->default(0);
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('process_caad', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('district_accountant');
            $table->dropColumn('business_hub_manager');
            $table->dropColumn('audit');
            $table->dropColumn('regional_manager');
            $table->dropColumn('hcs');
            $table->dropColumn('cco');
            $table->dropColumn('md');
        });
    
    }
};
