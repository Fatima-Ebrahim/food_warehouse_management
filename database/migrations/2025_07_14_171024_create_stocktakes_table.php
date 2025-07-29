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
        Schema::create('stocktakes', function (Blueprint $table) {
            $table->id();
            $table->timestamp('requested_at')->useCurrent();
            $table->enum('type', ['immediate', 'scheduled']);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();

            $table->enum('schedule_frequency', ['days', 'weeks', 'months', 'years'])->nullable();
            $table->integer('schedule_interval')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocktakes');
    }
};
