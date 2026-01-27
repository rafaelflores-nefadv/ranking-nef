<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\ApiOccurrence;
use App\Models\Seller;
use App\Services\ReportService;
use App\Services\SectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OccurrencesController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    /**
     * Exibe o relatório de ocorrências
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'supervisor'])) {
            abort(403, 'Acesso negado');
        }

        $allowedTeamIds = $user->getSupervisedTeamIds();
        $sectorId = app(SectorService::class)->resolveSectorIdForRequest($request);

        // Filtros
        $status = $request->query('status'); // pendente, processada, erro
        $startDate = $request->query('start_date') 
            ? Carbon::parse($request->query('start_date')) 
            : null;
        $endDate = $request->query('end_date') 
            ? Carbon::parse($request->query('end_date')) 
            : null;

        // Query base
        $query = ApiOccurrence::query()
            ->orderBy('created_at', 'desc');
        if ($sectorId) {
            $query->where('sector_id', $sectorId);
        }

        // Filtro por status
        if ($status === 'pendente') {
            $query->where('processed', false)->whereNull('error_message');
        } elseif ($status === 'processada') {
            $query->where('processed', true);
        } elseif ($status === 'erro') {
            $query->whereNotNull('error_message');
        }

        // Filtro por período
        if ($startDate) {
            $query->where('created_at', '>=', $startDate->startOfDay());
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate->endOfDay());
        }

        // Filtrar por equipes permitidas através dos vendedores
        if ($allowedTeamIds !== null) {
            $allowedSellers = Seller::where('sector_id', $sectorId)
                ->whereHas('teams', function ($q) use ($allowedTeamIds) {
                    $q->whereIn('teams.id', $allowedTeamIds);
                })
                ->get(['email', 'external_code']);

            $emails = $allowedSellers->pluck('email')->filter()->values()->all();
            $externalCodes = $allowedSellers->pluck('external_code')->filter()->values()->all();

            $query->where(function ($q) use ($emails, $externalCodes) {
                $q->where(function ($sub) use ($emails) {
                    $sub->where('collaborator_identifier_type', 'email')
                        ->whereIn('email_funcionario', $emails);
                })->orWhere(function ($sub) use ($externalCodes) {
                    $sub->where('collaborator_identifier_type', 'external_code')
                        ->whereIn('email_funcionario', $externalCodes);
                });
            });
        }

        $occurrences = $query->paginate(50);

        // Enriquecer com dados adicionais
        $occurrences->getCollection()->transform(function ($occurrence) {
            $seller = $occurrence->seller();
            $points = 0;
            
            if ($seller && $occurrence->processed) {
                // Buscar pontos atribuídos por esta ocorrência (aproximado)
                // Na prática, seria melhor ter uma relação direta
                $points = $seller->scores()
                    ->whereDate('created_at', $occurrence->created_at->format('Y-m-d'))
                    ->sum('points');
            }

            return [
                'id' => $occurrence->id,
                'email_funcionario' => $occurrence->email_funcionario,
                'ocorrencia' => $occurrence->ocorrencia,
                'credor' => $occurrence->credor,
                'equipe' => $occurrence->equipe,
                'status' => $this->reportService->getOccurrenceStatus($occurrence),
                'processed' => $occurrence->processed,
                'error_message' => $occurrence->error_message,
                'created_at' => Carbon::parse($occurrence->created_at),
                'points' => $points,
                'seller' => $seller,
            ];
        });

        // Exportação
        if ($request->query('export') === 'csv') {
            return $this->exportCsv($occurrences);
        }

        return view('reports.occurrences', compact(
            'occurrences',
            'status',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Exporta ocorrências para CSV
     */
    private function exportCsv($occurrences)
    {
        $filename = 'ocorrencias-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($occurrences) {
            $file = fopen('php://output', 'w');

            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Cabeçalhos
            fputcsv($file, [
                'Data/Hora',
                'Email Funcionário',
                'Ocorrência',
                'Credor',
                'Equipe',
                'Status',
                'Pontos Atribuídos',
                'Mensagem de Erro'
            ], ';');

            // Dados
            foreach ($occurrences->items() as $item) {
                fputcsv($file, [
                    $item['created_at']->format('d/m/Y H:i:s'),
                    $item['email_funcionario'],
                    $item['ocorrencia'],
                    $item['credor'] ?? '-',
                    $item['equipe'] ?? '-',
                    ucfirst($item['status']),
                    number_format($item['points'], 2, ',', '.'),
                    $item['error_message'] ?? '-'
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
