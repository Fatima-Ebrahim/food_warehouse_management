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
        Schema::create('shelves', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->foreignId('cabinet_id')->nullable()->constrained('cabinets')->nullOnDelete();
            $table->float('height')->default(0);
            $table->float('current_weight')->default(0);
            $table->float('max_weight')->default(0);
            $table->float('current_length')->default(0);
            $table->float('max_length')->default(0);
            $table->unsignedTinyInteger('levels')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shelves');
    }
};
