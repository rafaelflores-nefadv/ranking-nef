<div class="bg-slate-900/40 backdrop-blur-sm rounded-2xl p-4 border border-slate-700/50 max-h-[600px] overflow-y-auto custom-scrollbar">
    <h3 class="text-white font-semibold mb-4 text-sm">
        Classificação {{ $activeTeam ? $activeTeam->name : 'Geral' }}
    </h3>
    @if(count($ranking) > 0)
        <div class="space-y-2">
            @foreach($ranking as $index => $entry)
                @php
                    $position = $index + 1;
                    $positionColor = $position <= 3 ? 'from-blue-600 to-blue-500' : ($position <= 6 ? 'from-purple-600 to-purple-500' : 'from-slate-600 to-slate-500');
                    $initial = strtoupper(substr($entry['name'], 0, 1));
                @endphp
                <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-3 border border-slate-700/50 hover:border-blue-500/50 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="relative w-8 h-8 rounded-lg bg-gradient-to-br {{ $positionColor }} flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold text-sm">{{ $position }}</span>
                        </div>
                        <div class="relative">
                            <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-slate-600 group-hover:border-blue-500 transition-colors">
                                <div class="w-full h-full bg-gradient-to-br from-slate-700 to-slate-800 flex items-center justify-center">
                                    <span class="text-base font-bold text-white">{{ $initial }}</span>
                                </div>
                            </div>
                            <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 rounded-full border-2 border-slate-900"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-white font-semibold text-sm truncate">{{ $entry['name'] }}</h4>
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-slate-400">Pontos: <span class="text-blue-400 font-semibold">{{ number_format($entry['points'], 0, ',', '.') }}</span></span>
                                <span class="text-slate-500">•</span>
                                <span class="text-slate-400">Passados: <span class="text-slate-300">0</span></span>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            @if(rand(0, 1))
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                                    <polyline points="16 7 22 7 22 13"></polyline>
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <polyline points="22 17 13.5 8.5 8.5 13.5 2 7"></polyline>
                                    <polyline points="16 17 22 17 22 11"></polyline>
                                </svg>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-slate-400">
            <p class="text-sm">Nenhum participante encontrado</p>
        </div>
    @endif
</div>
