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
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->enum('type', [ 'storage',   'loading',  'receiving', 'processing', 'aisle' ])->nullable();
            $table->float('min_temperature')->nullable();
            $table->float('max_temperature')->nullable();
            $table->float('humidity_min')->nullable();
            $table->float('humidity_max')->nullable();
            $table->boolean('is_ventilated')->default(false);
            $table->boolean('is_shaded')->default(false);
            $table->boolean('is_dark')->default(false);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
