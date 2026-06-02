<?php

use App\Enums\ActivityStatus;
use App\Enums\BadgeType;
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
        Schema::create('providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('security_account_id')->constrained('security_accounts', 'user_id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('current_badge')->default(BadgeType::Grey->value);
            $table->timestampTz('badge_modified_at')->useCurrent();
            $table->timestampTz('badge_expires_at');
            $table->decimal('srt_score', 10, 4)->default(0);
            $table->unsignedInteger('missions_without_dispute_count')->default(0);
            $table->string('activity_status')->default(ActivityStatus::Available->value);
            $table->timestamps();

            $table->index('security_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
