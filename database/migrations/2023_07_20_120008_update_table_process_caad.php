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
            $table->string('batch_type')->default("single");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('process_caad', function (Blueprint $table) {
            $table->dropColumn('batch_type');
        });
    }
};
