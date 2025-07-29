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
        Schema::create('storage_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shelf_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedTinyInteger('position')->default(1);
            $table->timestamps();

            $table->unique(['shelf_id', 'level', 'position']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_locations');
    }
};
