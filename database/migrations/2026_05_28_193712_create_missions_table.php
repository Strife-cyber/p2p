<?php

use App\Enums\UrgencyLevel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignUuid('provider_id')->nullable()->constrained('providers')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignUuid('service_category_id')->constrained('service_categories')->restrictOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->text('intervention_address');
            $table->decimal('estimated_price', 12, 2);
            $table->decimal('final_price', 12, 2)->nullable();
            $table->string('urgency_level')->default(UrgencyLevel::Normal->value);
            $table->string('execution_mode');
            $table->string('lifecycle_status');
            $table->string('pairing_code', 25)->unique();
            $table->timestampTz('scheduled_at');
            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('warranty_expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('client_id');
            $table->index('provider_id');
        });

        DB::statement(
            "CREATE UNIQUE INDEX uq_active_mission_provider ON missions (provider_id) "
            ."WHERE provider_id IS NOT NULL AND lifecycle_status IN ('in_progress', 'assigned') AND deleted_at IS NULL"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
