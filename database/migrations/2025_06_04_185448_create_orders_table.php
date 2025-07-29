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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade');
            $table->enum('payment_type', ['cash', 'installment'])->default('cash'); // نوع الدفع
            $table->enum('payment_status', ['confirmed', 'paid', 'partially_paid'])->default('confirmed');
            $table->decimal('total_price', 10, 2);   // السعر قبل الخصم
            $table->unsignedInteger('used_points')->default(0); // عدد النقاط المستخدمة في هذا الطلب
            $table->decimal('final_price', 10, 2)->nullable();   // السعر بعد الخصم بالنقاط
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
