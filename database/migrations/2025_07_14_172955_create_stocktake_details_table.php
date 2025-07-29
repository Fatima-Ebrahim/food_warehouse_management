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
        Schema::create('stocktake_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stocktake_id');
            $table->unsignedBigInteger('item_id');
            $table->float('expected_quantity');
            $table->float('counted_quantity');
            $table->float('discrepancy');
            $table->timestamps();

            $table->foreign('stocktake_id')->references('id')->on('stocktakes')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocktake_details');
    }
};
