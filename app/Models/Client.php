<?php

namespace App\Models;

use App\Enums\ClientType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'security_account_id',
        'client_type',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'client_type' => ClientType::class,
        ];
    }

    public function securityAccount(): BelongsTo
    {
        return $this->belongsTo(SecurityAccount::class, 'security_account_id', 'user_id');
    }

    public function reliabilityEvents(): HasMany
    {
        return $this->hasMany(ClientReliabilityEvent::class);
    }

    public function missions(): HasMany
    {
        return $this->hasMany(Mission::class);
    }
}
