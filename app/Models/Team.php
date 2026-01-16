<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
    ];

    public function sellers(): HasMany
    {
        return $this->hasMany(Seller::class);
    }

    /**
     * Get the supervisors of this team.
     */
    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user');
    }
}
