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
        Schema::create('warehouse_coordinates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->foreignId('cabinet_id')->nullable()->constrained('cabinets')->nullOnDelete();
            $table->decimal('x', 10, 2)->default(0)->comment('coordinates X');
            $table->decimal('y', 10, 2)->default(0)->comment('coordinates Y');
            $table->decimal('z', 10, 2)->default(0)->comment('coordinates Z');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_coordinates');
    }
};
