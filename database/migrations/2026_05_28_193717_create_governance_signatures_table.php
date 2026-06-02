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
        Schema::create('governance_signatures', function (Blueprint $table) {
            $table->foreignUuid('governance_action_id')->constrained('governance_actions')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('founder_key', 50);
            $table->timestampTz('created_at')->useCurrent();

            $table->primary(['governance_action_id', 'founder_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governance_signatures');
    }
};
