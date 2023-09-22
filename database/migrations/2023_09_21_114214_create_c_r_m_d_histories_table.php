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
        Schema::create('crmd_history', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->default("null");
            $table->string('crmd_id')->default("null");
            $table->string('status')->default("null");
            $table->string('approval')->default("null");
            $table->string('comment')->default("null");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crmd_history');
    }
};
