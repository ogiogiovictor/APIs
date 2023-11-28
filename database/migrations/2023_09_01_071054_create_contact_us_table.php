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
        Schema::create('contact_us', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default(0);
            $table->string('message')->default(0);
            $table->string('email')->default(0);
            $table->string('accountType')->default(0);
            $table->string('unique_code')->default(0);
            $table->string('subject')->default(0);
            $table->string('status')->default(0);
            $table->string('phone')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_us');
    }
};
