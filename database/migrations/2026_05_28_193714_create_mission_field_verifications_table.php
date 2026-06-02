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
        Schema::create('mission_field_verifications', function (Blueprint $table) {
            $table->foreignUuid('mission_id')->primary()->constrained('missions')->cascadeOnDelete()->cascadeOnUpdate();
            $table->decimal('theoretical_latitude', 10, 6);
            $table->decimal('theoretical_longitude', 10, 6);
            $table->decimal('checkin_latitude', 10, 6)->nullable();
            $table->decimal('checkin_longitude', 10, 6)->nullable();
            $table->timestampTz('checked_in_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_field_verifications');
    }
};
