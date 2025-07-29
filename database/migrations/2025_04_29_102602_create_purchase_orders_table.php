<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->nullable()->unique();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->date('order_date');
            $table->date('expected_delivery_date');
            $table->enum('receipt_status', [
                'pending',
                'partial',
                'completed',
                'over_received',
                'cancelled',
                'price_changed',
                'quantity_changed'
            ])->default('pending');
            $table->date('receipt_date')->nullable();
            $table->string('receipt_number')->nullable()->unique();
            $table->text('order_notes')->nullable();
            $table->text('receipt_notes')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
