<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'scope',
        'season_id',
        'team_id',
        'seller_id',
        'name',
        'description',
        'target_value',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Verifica se a meta está ativa (dentro do período de validade)
     */
    public function isActive(): bool
    {
        $now = now()->startOfDay();
        return $now >= $this->starts_at && $now <= $this->ends_at;
    }

    /**
     * Verifica se a meta foi atingida
     */
    public function isReached(float $currentValue): bool
    {
        return $currentValue >= $this->target_value;
    }

    /**
     * Calcula o progresso percentual
     */
    public function getProgress(float $currentValue): float
    {
        if ($this->target_value <= 0) {
            return 0;
        }
        
        $progress = ($currentValue / $this->target_value) * 100;
        return round(min(100, max(0, $progress)), 2);
    }
}
