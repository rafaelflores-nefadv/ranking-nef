<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'api_occurrence_id',
        'sector_id',
        'seller_id',
        'score_rule_id',
        'points',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function scoreRule(): BelongsTo
    {
        return $this->belongsTo(ScoreRule::class);
    }
}
