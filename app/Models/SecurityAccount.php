<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SecurityAccount extends Model
{
    /** @use HasFactory<\Database\Factories\SecurityAccountFactory> */
    use HasFactory;

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'real_phone',
        'proxy_number',
        'device_fingerprint',
        'national_id_hash',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): HasOne
    {
        return $this->hasOne(Provider::class, 'security_account_id', 'user_id');
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class, 'security_account_id', 'user_id');
    }
}
