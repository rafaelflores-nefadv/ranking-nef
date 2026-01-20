<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ApiToken extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'integration_id',
        'token',
        'secret_hash',
        'last_used_at',
        'is_active',
    ];

    protected $hidden = [
        'secret_hash',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the integration that owns the token.
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(ApiIntegration::class, 'integration_id');
    }

    /**
     * Generate a new token and secret.
     * Returns an array with 'token' and 'secret' keys.
     */
    public static function generate(): array
    {
        return [
            'token' => 'rknf_' . Str::random(32),
            'secret' => Str::random(64),
        ];
    }

    /**
     * Verify if the provided secret matches the stored hash.
     */
    public function verifySecret(string $secret): bool
    {
        return Hash::check($secret, $this->secret_hash);
    }

    /**
     * Set the secret hash from a plain secret.
     */
    public function setSecretHashFromPlainSecret(string $secret): void
    {
        $this->secret_hash = Hash::make($secret);
    }
}
