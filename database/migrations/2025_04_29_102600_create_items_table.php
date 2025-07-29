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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->foreignId('base_unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('minimum_stock_level', 10, 2)->default(0);
            $table->decimal('maximum_stock_level', 10, 2)->nullable();
            $table->json('storage_conditions')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->cascadeOnDelete();
            $table->string('image')->nullable();
            $table->integer('Total_Available_Quantity')->default(0);
            $table->string('barcode')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'code']);
            $table->index(['category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
