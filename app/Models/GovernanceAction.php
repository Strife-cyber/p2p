<?php

namespace App\Models;

use App\Enums\GovernanceActionStatus;
use App\Enums\GovernanceActionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GovernanceAction extends Model
{
    /** @use HasFactory<\Database\Factories\GovernanceActionFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'action_type',
        'raw_payload',
        'action_status',
        'created_executed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action_type' => GovernanceActionType::class,
            'action_status' => GovernanceActionStatus::class,
            'created_executed_at' => 'datetime',
        ];
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(GovernanceSignature::class);
    }
}
