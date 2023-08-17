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
        Schema::create('payment_logs_three', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('amount')->nullable();
            $table->string('account_type')->nullable();
            $table->string('account_number')->nullable();
            $table->string('meter_no')->nullable();
            $table->enum('status', ['pending', 'started', 'success', 'failed'])->default('pending');
            $table->string('customer_name')->nullable();
            $table->string('payment_source')->nullable();
            $table->string('provider')->nullable();
            $table->string('providerRef')->nullable();
            $table->timestamp('date_entered')->nullable();
            $table->timestamp('receiptno')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_logs_three');
    }
};
