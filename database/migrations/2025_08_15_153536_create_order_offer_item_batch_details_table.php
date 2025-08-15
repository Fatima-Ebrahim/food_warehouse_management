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
        Schema::create('order_offer_item_batch_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_offer_id')->
                constrained('order_offers')->onDelete('cascade');
            $table->foreignId('order_offer_Items_id')->
                constrained('special_offer_items')->onDelete('cascade');
            $table->foreignId('purchase_receipt_item_id')->
                constrained('purchase_receipt_items')->onDelete('restrict');
            $table->decimal('quantity', 10, 3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_offer_item_batch_details');
    }
};
