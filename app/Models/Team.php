<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'sector_id',
        'name',
    ];

    /**
     * Get the sellers that belong to this team.
     */
    public function sellers(): BelongsToMany
    {
        return $this->belongsToMany(Seller::class, 'seller_team');
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    /**
     * Get the supervisors of this team.
     */
    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user');
    }
}
