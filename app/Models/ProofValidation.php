<?php

namespace App\Models;

use App\Enums\FlowType;
use App\Enums\ValidationResult;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProofValidation extends Model
{
    /** @use HasFactory<\Database\Factories\ProofValidationFactory> */
    use HasFactory;

    protected $primaryKey = 'proof_file_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'proof_file_id',
        'flow_type',
        'validation_result',
        'validator_id',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'flow_type' => FlowType::class,
            'validation_result' => ValidationResult::class,
            'created_at' => 'datetime',
        ];
    }

    public function proofFile(): BelongsTo
    {
        return $this->belongsTo(ProofFile::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_id');
    }
}
