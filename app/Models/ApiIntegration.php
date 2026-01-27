<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiIntegration extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'sector_id',
        'name',
        'description',
        'system',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the tokens for the integration.
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(ApiToken::class, 'integration_id');
    }

    /**
     * Get active tokens for the integration.
     */
    public function activeTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class, 'integration_id')->where('is_active', true);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }
}
