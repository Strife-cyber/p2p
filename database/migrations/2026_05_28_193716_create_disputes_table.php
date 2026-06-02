<?php

use App\Enums\DisputeStatus;
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
        Schema::create('disputes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mission_id')->unique()->constrained('missions')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('anomaly_type');
            $table->string('dispute_status')->default(DisputeStatus::Open->value);
            $table->foreignUuid('arbitrator_id')->nullable()->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->text('decision_notes')->nullable();
            $table->decimal('srt_penalty', 10, 4)->default(0);
            $table->timestampTz('triggered_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->softDeletes();

            $table->index('arbitrator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
