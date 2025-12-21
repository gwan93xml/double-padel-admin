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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade');
            $table->string('transaction_id')->unique();
            $table->string('midtrans_order_id')->nullable();
            $table->integer('amount');
            $table->enum('status', ['pending', 'settlement', 'deny', 'cancel', 'expire', 'failure'])->default('pending');
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('restrict');
            $table->json('midtrans_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->string('qris_url')->nullable();
            $table->string('bank')->nullable();
            $table->string('va_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
