<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'starts_at',
        'ends_at',
        'is_active',
        'recurrence_type',
        'fixed_end_date',
        'duration_days',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
            'fixed_end_date' => 'date',
            'duration_days' => 'integer',
        ];
    }

    public function sellers(): HasMany
    {
        return $this->hasMany(Seller::class);
    }

    /**
     * Calcula o tempo restante da temporada formatado
     * Retorna formato: "X sem Yd" (X semanas Y dias)
     */
    public function getRemainingTimeFormatted(): string
    {
        $now = now()->startOfDay();
        $endDate = $this->ends_at;
        
        if ($now >= $endDate) {
            return '0 sem 0d';
        }
        
        $diff = $now->diffInDays($endDate);
        $weeks = floor($diff / 7);
        $days = $diff % 7;
        
        return "{$weeks} sem {$days}d";
    }

    /**
     * Calcula o percentual de progresso da temporada (0-100)
     */
    public function getProgressPercentage(): float
    {
        $now = now()->startOfDay();
        $start = $this->starts_at;
        $end = $this->ends_at;
        
        if ($now < $start) {
            return 0;
        }
        
        if ($now >= $end) {
            return 100;
        }
        
        $totalDays = $start->diffInDays($end);
        $elapsedDays = $start->diffInDays($now);
        
        if ($totalDays <= 0) {
            return 100;
        }
        
        return round(($elapsedDays / $totalDays) * 100, 2);
    }

    /**
     * Calcula o offset do círculo SVG para o progresso visual
     * Retorna valor entre 0 e 351.86 (circunferência do círculo)
     */
    public function getProgressCircleOffset(): float
    {
        $progress = $this->getProgressPercentage();
        $circumference = 351.86; // 2 * PI * 56 (raio)
        $offset = $circumference - ($progress / 100 * $circumference);
        return max(0, min($circumference, $offset));
    }

    /**
     * Calcula as datas de início e fim baseado no tipo de recorrência
     * 
     * @param string $recurrenceType Tipo de recorrência (daily, weekly, monthly, etc.)
     * @param \Carbon\Carbon|null $startDate Data de início (padrão: hoje)
     * @param string|null $fixedEndDate Data fixa (para fixed_date)
     * @param int|null $durationDays Duração em dias (para days)
     * @return array ['starts_at' => Carbon, 'ends_at' => Carbon]
     */
    public static function calculateDatesByRecurrence(
        string $recurrenceType,
        ?\Carbon\Carbon $startDate = null,
        ?string $fixedEndDate = null,
        ?int $durationDays = null
    ): array {
        $startDate = $startDate ?? now()->startOfDay();
        $startsAt = $startDate->copy();
        $endsAt = null;

        switch ($recurrenceType) {
            case 'daily':
                $endsAt = $startsAt->copy()->endOfDay();
                break;
            
            case 'weekly':
                // Semana: ajustar início para início da semana e fim para fim da semana
                $startsAt = $startsAt->copy()->startOfWeek();
                $endsAt = $startsAt->copy()->endOfWeek();
                break;
            
            case 'monthly':
                // Mensal: ajustar início para início do mês e fim para fim do mês
                $startsAt = $startsAt->copy()->startOfMonth();
                $endsAt = $startsAt->copy()->endOfMonth();
                break;
            
            case 'bimonthly':
                // Bimestral: do início do mês atual até o fim do mês + 1
                $startsAt = $startsAt->copy()->startOfMonth();
                $endsAt = $startsAt->copy()->addMonths(2)->subDay()->endOfDay();
                break;
            
            case 'quarterly':
                // Trimestral: ajustar início para início do trimestre e fim para fim do trimestre
                $startsAt = $startsAt->copy()->startOfQuarter();
                $endsAt = $startsAt->copy()->endOfQuarter();
                break;
            
            case 'semiannual':
                // Semestral: do início do mês atual até 6 meses depois
                $startsAt = $startsAt->copy()->startOfMonth();
                $endsAt = $startsAt->copy()->addMonths(6)->subDay()->endOfDay();
                break;
            
            case 'annual':
                // Anual: ajustar início para início do ano e fim para fim do ano
                $startsAt = $startsAt->copy()->startOfYear();
                $endsAt = $startsAt->copy()->endOfYear();
                break;
            
            case 'fixed_date':
                if ($fixedEndDate) {
                    $endsAt = \Carbon\Carbon::parse($fixedEndDate)->endOfDay();
                } else {
                    // Fallback: usar fim do mês atual
                    $endsAt = $startsAt->copy()->endOfMonth();
                }
                break;
            
            case 'days':
                if ($durationDays && $durationDays > 0) {
                    $endsAt = $startsAt->copy()->addDays($durationDays - 1)->endOfDay();
                } else {
                    // Fallback: usar 30 dias
                    $endsAt = $startsAt->copy()->addDays(29)->endOfDay();
                }
                break;
            
            default:
                // Fallback: usar fim do mês atual
                $endsAt = $startsAt->copy()->endOfMonth();
                break;
        }

        return [
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ];
    }
}
