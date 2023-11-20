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
            $table->string('old_mobile')->default("null");
            $table->string('tarriffcode')->default("null");
            $table->string('new_tarriff_code')->default("null");
            $table->string('email')->default("null");
            $table->string('new_email')->default("null");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crmdcustomers', function (Blueprint $table) {
            $table->dropColumn('old_mobile');
            $table->dropColumn('tarriffcode');
            $table->dropColumn('new_tarriff_code');
            $table->dropColumn('email');
            $table->dropColumn('new_email');
        });
    }
};
