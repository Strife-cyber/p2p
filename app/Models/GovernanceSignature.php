<?php

namespace App\Models;

use App\Enums\FounderKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GovernanceSignature extends Model
{
    /** @use HasFactory<\Database\Factories\GovernanceSignatureFactory> */
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'governance_action_id',
        'founder_key',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'founder_key' => FounderKey::class,
            'created_at' => 'datetime',
        ];
    }

    protected function setKeysForSaveQuery($query)
    {
        $query->where('governance_action_id', $this->getAttribute('governance_action_id'))
            ->where('founder_key', $this->getAttribute('founder_key'));

        return $query;
    }

    public function governanceAction(): BelongsTo
    {
        return $this->belongsTo(GovernanceAction::class);
    }
}
