<?php

namespace App\Models;

use App\Enums\AnomalyType;
use App\Enums\DisputeStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dispute extends Model
{
    /** @use HasFactory<\Database\Factories\DisputeFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    public const CREATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'mission_id',
        'anomaly_type',
        'dispute_status',
        'arbitrator_id',
        'decision_notes',
        'srt_penalty',
        'triggered_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'anomaly_type' => AnomalyType::class,
            'dispute_status' => DisputeStatus::class,
            'srt_penalty' => 'decimal:4',
            'triggered_at' => 'datetime',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function arbitrator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'arbitrator_id');
    }
}
