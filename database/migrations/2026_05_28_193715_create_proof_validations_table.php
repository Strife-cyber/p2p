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
        Schema::create('proof_validations', function (Blueprint $table) {
            $table->foreignUuid('proof_file_id')->primary()->constrained('proof_files')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('flow_type');
            $table->string('validation_result');
            $table->foreignUuid('validator_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestampTz('created_at')->useCurrent();

            $table->index('validator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proof_validations');
    }
};
