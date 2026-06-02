<?php

namespace App\Models;

use App\Enums\ExecutionMode;
use App\Enums\LifecycleStatus;
use App\Enums\UrgencyLevel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mission extends Model
{
    /** @use HasFactory<\Database\Factories\MissionFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'provider_id',
        'service_category_id',
        'title',
        'description',
        'intervention_address',
        'estimated_price',
        'final_price',
        'urgency_level',
        'execution_mode',
        'lifecycle_status',
        'pairing_code',
        'scheduled_at',
        'completed_at',
        'warranty_expires_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estimated_price' => 'decimal:2',
            'final_price' => 'decimal:2',
            'urgency_level' => UrgencyLevel::class,
            'execution_mode' => ExecutionMode::class,
            'lifecycle_status' => LifecycleStatus::class,
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'warranty_expires_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function evaluation(): HasOne
    {
        return $this->hasOne(Evaluation::class);
    }

    public function fieldVerification(): HasOne
    {
        return $this->hasOne(MissionFieldVerification::class);
    }

    public function proofFiles(): HasMany
    {
        return $this->hasMany(ProofFile::class);
    }

    public function escrowLedger(): HasOne
    {
        return $this->hasOne(EscrowLedger::class);
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(MissionApplication::class);
    }

    public static function generatePairingCode(): string
    {
        return strtoupper(Str::random(8));
    }
}
