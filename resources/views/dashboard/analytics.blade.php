@extends('layouts.app')

@section('title', 'Dashboard - Game League')

@section('content')
<div class="min-h-screen bg-[#0a0e1a]">
    <!-- Header -->
    <div class="bg-slate-900/80 backdrop-blur-md border-b border-slate-800/50 px-8 py-6">
        <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white mb-1">Análise de pontuação e margem</h1>
            <p class="text-sm text-slate-400">Visão geral dos indicadores financeiros</p>
        </div>
        
        <div class="flex items-center gap-4">
            <!-- Filtros -->
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-3">
                <select name="month" class="px-4 py-2 bg-slate-800/60 border border-slate-700/50 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50" onchange="this.form.submit()">
                    <option value="">Todos os meses</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ (int)($filters['month'] ?? '') === $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $i, 1)->locale('pt_BR')->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                
                <select name="team" class="px-4 py-2 bg-slate-800/60 border border-slate-700/50 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50" onchange="this.form.submit()">
                    <option value="">Todas as equipes</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}" {{ ($filters['team_id'] ?? '') === $team->id ? 'selected' : '' }}>
                            {{ $team->display_label }}
                        </option>
                    @endforeach
                </select>
                
                <select name="season" class="px-4 py-2 bg-slate-800/60 border border-slate-700/50 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50" onchange="this.form.submit()">
                    <option value="">Todas as temporadas</option>
                    @foreach($seasons as $season)
                        <option value="{{ $season->id }}" {{ ($filters['season_id'] ?? '') === $season->id ? 'selected' : '' }}>
                            {{ $season->name }}
                        </option>
                    @endforeach
                </select>
                
                @if($filters['month'] || $filters['team_id'] || $filters['season_id'])
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-red-600/20 border border-red-500/30 rounded-lg text-red-400 text-sm hover:bg-red-600/30 transition-colors">
                        Limpar filtros
                    </a>
                @endif
            </form>
            <a href="{{ route('reports.ranking-general') }}" target="_blank" rel="noopener" class="px-4 py-2 border border-slate-700/60 rounded-lg text-sm font-semibold text-slate-200 hover:bg-slate-800/60 hover:border-slate-500 transition-colors">
                Abrir Relatório
            </a>
            
            <!-- Branding -->
            <div class="px-4 py-2 bg-slate-800/60 border border-slate-700/50 rounded-lg">
                <span class="text-white font-semibold text-sm">Game League</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="p-8">
    <!-- Cards Superiores -->
    <div class="grid grid-cols-3 gap-6 mb-8">
        <!-- Card Pontuação Operacional -->
        <div class="bg-gradient-to-br from-blue-600/20 to-blue-800/20 backdrop-blur-sm rounded-2xl border border-blue-500/30 p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-blue-300 uppercase tracking-wide">Pontuação Operacional</h3>
                <div class="w-10 h-10 rounded-lg bg-blue-600/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mb-4">
                <p class="text-3xl font-bold text-white mb-1">{{ number_format($total_receita, 0, ',', '.') }} pts</p>
                <p class="text-xs text-blue-300/80">
                    @if($melhor_mes)
                        Melhor mês: <span class="font-semibold">{{ ucfirst($melhor_mes['mes_nome']) }} {{ $melhor_mes['ano'] }}</span>
                    @else
                        Sem dados disponíveis
                    @endif
                </p>
            </div>
            <div class="h-16">
                <canvas id="chart-receita"></canvas>
            </div>
        </div>

        <!-- Card Margem de Contribuição -->
        <div class="bg-gradient-to-br from-green-600/20 to-green-800/20 backdrop-blur-sm rounded-2xl border border-green-500/30 p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-green-300 uppercase tracking-wide">Margem de Pontuação</h3>
                <div class="w-10 h-10 rounded-lg bg-green-600/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
            <div class="mb-4">
                <p class="text-3xl font-bold text-white mb-1">{{ number_format($margem_contribuicao, 0, ',', '.') }} pts</p>
                <p class="text-xs text-green-300/80">
                    @if($melhor_mes)
                        Melhor mês: <span class="font-semibold">{{ ucfirst($melhor_mes['mes_nome']) }} {{ $melhor_mes['ano'] }}</span>
                    @else
                        Sem dados disponíveis
                    @endif
                </p>
            </div>
            <div class="h-16">
                <canvas id="chart-margem"></canvas>
            </div>
        </div>

        <!-- Card % MC Geral -->
        <div class="bg-gradient-to-br from-pink-600/20 to-pink-800/20 backdrop-blur-sm rounded-2xl border border-pink-500/30 p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-pink-300 uppercase tracking-wide">% MC Geral</h3>
                <div class="w-10 h-10 rounded-lg bg-pink-600/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mb-4">
                <p class="text-3xl font-bold text-white mb-1">{{ number_format($percentual_mc, 1) }}%</p>
                <p class="text-xs text-pink-300/80">
                    @if($melhor_mes)
                        Melhor mês: <span class="font-semibold">{{ ucfirst($melhor_mes['mes_nome']) }} {{ $melhor_mes['ano'] }}</span>
                    @else
                        Sem dados disponíveis
                    @endif
                </p>
            </div>
            <div class="h-16">
                <canvas id="chart-percentual"></canvas>
            </div>
        </div>
    </div>

    <!-- Área Inferior -->
    <div class="grid grid-cols-2 gap-6">
        <!-- Tabela de Status -->
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-slate-700/50 p-6 shadow-xl">
            <h3 class="text-lg font-bold text-white mb-4">Status</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-700/50">
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wide">Status</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wide">Pontuação</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wide">MC</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wide">% MC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($top_fornecedores as $fornecedor)
                            <tr class="border-b border-slate-800/50 hover:bg-slate-800/30 transition-colors">
                                <td class="py-3 px-4 text-white font-medium">{{ $fornecedor['fornecedor'] }}</td>
                                <td class="py-3 px-4 text-right text-white">{{ number_format($fornecedor['receita'], 0, ',', '.') }} pts</td>
                                <td class="py-3 px-4 text-right text-green-400">{{ number_format($fornecedor['margem'], 0, ',', '.') }} pts</td>
                                <td class="py-3 px-4 text-right text-blue-400">{{ number_format($fornecedor['percentual_mc'], 1) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 px-4 text-center text-slate-400">
                                    Nenhum dado disponível
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Gráfico de Barras Horizontais -->
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-slate-700/50 p-6 shadow-xl">
            <h3 class="text-lg font-bold text-white mb-4">Margem de Pontuação por Status</h3>
            <div class="h-80">
                <canvas id="chart-barras"></canvas>
            </div>
        </div>
    </div>
</div>

@php
    $dashboardDataJson = json_encode($dashboard_data ?? [], JSON_UNESCAPED_UNICODE);
@endphp
@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Dados do backend - APENAS renderização, sem cálculos
window.DASHBOARD_DATA = <?php echo $dashboardDataJson; ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Extrair dados do objeto global (apenas para renderização)
    const evolucaoMensal = window.DASHBOARD_DATA.evolucao_mensal || [];
    const meses = evolucaoMensal.map(m => m.mes_nome);
    const receitaData = evolucaoMensal.map(m => m.receita);
    const margemData = evolucaoMensal.map(m => m.margem);
    const percentualData = evolucaoMensal.map(m => m.percentual);
    
    const topFornecedores = window.DASHBOARD_DATA.top_fornecedores || [];
    const fornecedores = topFornecedores.map(f => f.fornecedor);
    const margemFornecedores = topFornecedores.map(f => f.margem);

    // Configuração comum para gráficos de linha
    const lineChartConfig = {
        type: 'line',
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: false
                }
            },
            scales: {
                x: {
                    display: false
                },
                y: {
                    display: false
                }
            },
            elements: {
                point: {
                    radius: 0
                },
                line: {
                    tension: 0.4,
                    borderWidth: 2
                }
            }
        }
    };

    // Gráfico Pontuação Operacional
    new Chart(document.getElementById('chart-receita'), {
        ...lineChartConfig,
        data: {
            labels: meses,
            datasets: [{
                label: 'Pontuação',
                data: receitaData,
                borderColor: 'rgb(96, 165, 250)',
                backgroundColor: 'rgba(96, 165, 250, 0.1)',
                fill: true
            }]
        }
    });

    // Gráfico Margem de Contribuição
    new Chart(document.getElementById('chart-margem'), {
        ...lineChartConfig,
        data: {
            labels: meses,
            datasets: [{
                label: 'Margem de Pontuação',
                data: margemData,
                borderColor: 'rgb(74, 222, 128)',
                backgroundColor: 'rgba(74, 222, 128, 0.1)',
                fill: true
            }]
        }
    });

    // Gráfico % MC Geral
    new Chart(document.getElementById('chart-percentual'), {
        ...lineChartConfig,
        data: {
            labels: meses,
            datasets: [{
                label: '% MC',
                data: percentualData,
                borderColor: 'rgb(244, 114, 182)',
                backgroundColor: 'rgba(244, 114, 182, 0.1)',
                fill: true
            }]
        }
    });

    // Gráfico de Barras Horizontais
    new Chart(document.getElementById('chart-barras'), {
        type: 'bar',
        data: {
            labels: fornecedores,
            datasets: [{
                label: 'Margem de Pontuação',
                data: margemFornecedores,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(59, 130, 246, 0.6)',
                    'rgba(59, 130, 246, 0.5)',
                    'rgba(59, 130, 246, 0.4)'
                ],
                borderColor: [
                    'rgb(59, 130, 246)',
                    'rgb(59, 130, 246)',
                    'rgb(59, 130, 246)',
                    'rgb(59, 130, 246)',
                    'rgb(59, 130, 246)'
                ],
                borderWidth: 1,
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            return 'Margem de Pontuação: ' + context.parsed.x.toLocaleString('pt-BR') + ' pts';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        color: 'rgba(148, 163, 184, 0.8)',
                        callback: function(value) {
                            if (value >= 1000) {
                                return (value / 1000).toFixed(1).replace('.', ',') + 'k pts';
                            }
                            return value.toLocaleString('pt-BR') + ' pts';
                        }
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)'
                    }
                },
                y: {
                    ticks: {
                        color: 'rgba(148, 163, 184, 0.8)'
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)'
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
