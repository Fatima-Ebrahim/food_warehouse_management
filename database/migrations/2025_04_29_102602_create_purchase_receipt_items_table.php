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
        Schema::create('purchase_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('quantity', 12, 3);
            $table->decimal('available_quantity',12,3)->default(0);
            $table->decimal('price', 10, 2);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->decimal('unit_weight', 10, 3)->nullable()->default(0);
            $table->decimal('total_weight', 12, 3)->nullable()->default(0);
            $table->date('production_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['purchase_order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('purchase_receipt_items');
    }
};
