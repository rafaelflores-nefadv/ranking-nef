<?php

namespace App\Services;

use App\Models\ApiOccurrence;
use App\Models\Seller;
use App\Models\Team;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Filtra equipes permitidas para o usuário
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|null $allowedTeamIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterTeamsByUser($query, ?array $allowedTeamIds)
    {
        if ($allowedTeamIds !== null) {
            // Supervisor: apenas suas equipes
            $query->whereIn('team_id', $allowedTeamIds);
        }
        // Admin: todas as equipes (sem filtro)
        
        return $query;
    }

    /**
     * Calcula status de ocorrências
     *
     * @param ApiOccurrence $occurrence
     * @return string
     */
    public function getOccurrenceStatus(ApiOccurrence $occurrence): string
    {
        if ($occurrence->processed) {
            return 'processada';
        }
        
        if ($occurrence->error_message) {
            return 'erro';
        }
        
        return 'pendente';
    }
}
