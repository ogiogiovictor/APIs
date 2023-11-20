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
        Schema::table('crmdcustomers_files', function (Blueprint $table) {
          
            $table->string('document_type')->default("null");
            $table->string('account_no')->default("null");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crmdcustomers_files', function (Blueprint $table) {
            $table->dropColumn('document_type');
            $table->dropColumn('account_no');
        });
    }
};
