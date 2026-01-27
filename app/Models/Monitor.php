<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * Retorna as configurações padrão do monitor
     */
    public static function getDefaultSettings(): array
    {
        return [
            'refresh_interval' => 30000, // 30 segundos
            'auto_rotate_teams' => true,
            'teams' => [], // IDs das equipes para rotacionar (vazio = todas)
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
