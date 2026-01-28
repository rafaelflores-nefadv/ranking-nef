<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Monitor extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'sector_id',
        'name',
        'slug',
        'description',
        'settings',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function sectors(): BelongsToMany
    {
        return $this->belongsToMany(Sector::class, 'monitor_sector')
            ->withTimestamps();
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'monitor_team')
            ->withTimestamps();
    }

    /**
     * Setores efetivos do monitor (pivot; fallback para coluna legacy).
     */
    public function getSectorIds(): array
    {
        $ids = $this->relationLoaded('sectors')
            ? $this->sectors->pluck('id')->all()
            : $this->sectors()->pluck('sectors.id')->all();

        if (empty($ids) && !empty($this->sector_id)) {
            $ids = [$this->sector_id];
        }

        $ids = array_values(array_unique(array_filter($ids)));

        return $ids;
    }

    /**
     * Equipes explicitamente configuradas (pivot; fallback para settings['teams']).
     * Retorna array de IDs. Vazio significa: "todas as equipes dos setores do monitor".
     */
    public function getAllowedTeamIds(): array
    {
        $ids = $this->relationLoaded('teams')
            ? $this->teams->pluck('id')->all()
            : $this->teams()->pluck('teams.id')->all();

        if (empty($ids)) {
            $settings = $this->getMergedSettings();
            $ids = $settings['teams'] ?? [];
        }

        if (!is_array($ids)) {
            $ids = [];
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * Retorna as configurações padrão do monitor
     */
    public static function getDefaultSettings(): array
    {
        return [
            'refresh_interval' => 30000, // 30 segundos
            'auto_rotate_teams' => true,
            'teams' => [], // legacy (mantido para compatibilidade; preferir pivot monitor_team)
            'notifications_enabled' => false,
            'sound_enabled' => false,
            'voice_enabled' => false, // Leitura por voz do ranking
            'font_scale' => 1.0, // Escala de fonte para TV
        ];
    }

    /**
     * Obtém configurações do monitor mescladas com padrões
     */
    public function getMergedSettings(): array
    {
        $defaults = self::getDefaultSettings();
        $settings = $this->settings ?? [];
        
        $merged = array_merge($defaults, $settings);
        
        // Garantir que valores booleanos sejam booleanos reais (não strings)
        $booleanFields = ['auto_rotate_teams', 'notifications_enabled', 'sound_enabled', 'voice_enabled'];
        foreach ($booleanFields as $field) {
            if (isset($merged[$field])) {
                $value = $merged[$field];
                if (is_string($value)) {
                    $merged[$field] = in_array(strtolower($value), ['true', '1', 'yes', 'on']);
                } elseif (is_int($value)) {
                    $merged[$field] = $value === 1;
                } else {
                    $merged[$field] = (bool)$value;
                }
            }
        }
        
        return $merged;
    }
}
