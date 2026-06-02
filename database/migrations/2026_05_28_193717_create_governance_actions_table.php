<?php

use App\Enums\GovernanceActionStatus;
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
        Schema::create('governance_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('action_type');
            $table->text('raw_payload');
            $table->string('action_status')->default(GovernanceActionStatus::PendingSignatures->value);
            $table->timestampTz('created_executed_at')->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governance_actions');
    }
};
