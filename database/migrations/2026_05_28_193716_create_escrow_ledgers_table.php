<?php

use App\Enums\EscrowStatus;
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
        Schema::create('escrow_ledgers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mission_id')->unique()->constrained('missions')->cascadeOnDelete()->cascadeOnUpdate();
            $table->decimal('total_amount', 12, 2);
            $table->string('escrow_status')->default(EscrowStatus::Blocked->value);
            $table->string('transaction_reference', 100);
            $table->timestampTz('locked_at')->useCurrent();
            $table->timestampTz('released_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escrow_ledgers');
    }
};
