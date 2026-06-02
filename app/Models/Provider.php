<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use App\Enums\BadgeType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    /** @use HasFactory<\Database\Factories\ProviderFactory> */
    use HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'security_account_id',
        'current_badge',
        'badge_modified_at',
        'badge_expires_at',
        'srt_score',
        'missions_without_dispute_count',
        'activity_status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_badge' => BadgeType::class,
            'badge_modified_at' => 'datetime',
            'badge_expires_at' => 'datetime',
            'srt_score' => 'decimal:4',
            'activity_status' => ActivityStatus::class,
        ];
    }

    public function securityAccount(): BelongsTo
    {
        return $this->belongsTo(SecurityAccount::class, 'security_account_id', 'user_id');
    }

    public function serviceCategories(): BelongsToMany
    {
        return $this->belongsToMany(ServiceCategory::class, 'provider_skills')
            ->withPivot('created_at');
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(ProviderTracking::class);
    }

    public function missions(): HasMany
    {
        return $this->hasMany(Mission::class);
    }
}
