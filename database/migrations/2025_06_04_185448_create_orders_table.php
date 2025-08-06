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
            $table->enum('payment_type', ['cash', 'installment'])->default('cash');
            $table->enum('status', ['pending','rejected','confirmed', 'paid', 'partially_paid'])
                ->default('confirmed');
            $table->decimal('total_price', 10, 2);
            $table->unsignedInteger('used_points')->default(0);
            $table->decimal('final_price', 10, 2)->nullable();
            $table->string('qr_code_path')->nullable(); // مسار حفظ qr code
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
