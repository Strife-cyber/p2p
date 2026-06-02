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
        Schema::create('security_accounts', function (Blueprint $table) {
            $table->foreignUuid('user_id')->primary()->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('real_phone', 25)->unique();
            $table->string('proxy_number', 25)->unique();
            $table->string('device_fingerprint', 255)->unique();
            $table->string('national_id_hash', 255)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_accounts');
    }
};
