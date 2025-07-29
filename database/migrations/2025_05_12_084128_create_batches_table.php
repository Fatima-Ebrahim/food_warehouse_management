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

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('storage_unit_id')->constrained('units')->onDelete('cascade');
            $table->string('batch_number')->nullable();
            $table->date('production_date');
            $table->date('expiry_date');
            $table->foreignId('purchase_receipt_item_id')->nullable()->constrained('purchase_receipt_items')->onDelete('cascade');
            $table->timestamps();

            $table->index(['item_id', 'expiry_date']);
            $table->index([ 'batch_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
