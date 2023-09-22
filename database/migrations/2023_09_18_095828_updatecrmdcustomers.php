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
            $table->string('new_firstname')->default("null");
            $table->string('new_surname')->default("null");
            $table->string('new_address')->default("null");
            $table->string('mobile')->default(0);
            $table->string('new_mobile')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crmdcustomers', function (Blueprint $table) {
            $table->dropColumn('new_firstname');
            $table->dropColumn('new_surname');
            $table->dropColumn('new_address');
            $table->dropColumn('mobile');
            $table->dropColumn('new_mobile');
        });
    
    }
};
