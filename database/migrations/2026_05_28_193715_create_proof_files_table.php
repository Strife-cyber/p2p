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
        Schema::create('proof_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mission_id')->constrained('missions')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('storage_url', 512);
            $table->timestampTz('captured_at')->useCurrent();

            $table->index('mission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proof_files');
    }
};
