<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'sector_id',
        'season_id',
        'name',
        'email',
        'external_code',
        'avatar',
        'points',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
        ];
    }

    /**
     * Get the teams that the seller belongs to.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'seller_team');
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    /**
     * Get the primary team (for backward compatibility).
     * Accessor that returns the first team.
     */
    public function getTeamAttribute(): ?Team
    {
        return $this->teams->first();
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }
}
