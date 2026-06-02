<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionFieldVerification extends Model
{
    /** @use HasFactory<\Database\Factories\MissionFieldVerificationFactory> */
    use HasFactory;

    protected $primaryKey = 'mission_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'mission_id',
        'theoretical_latitude',
        'theoretical_longitude',
        'checkin_latitude',
        'checkin_longitude',
        'checked_in_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'theoretical_latitude' => 'decimal:6',
            'theoretical_longitude' => 'decimal:6',
            'checkin_latitude' => 'decimal:6',
            'checkin_longitude' => 'decimal:6',
            'checked_in_at' => 'datetime',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }
}
