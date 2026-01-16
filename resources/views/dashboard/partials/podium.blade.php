@if(count($top3) > 0)
    <div class="relative py-12">
        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-[600px] h-32">
            <div class="relative w-full h-full">
                <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/20 via-blue-500/30 to-purple-500/20 blur-xl"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-96 h-4 rounded-full border-2 border-cyan-400/40 bg-cyan-500/10"></div>
                </div>
            </div>
        </div>
        
        <div class="flex items-end justify-center gap-8 relative z-10">
            @if(isset($top3[1]))
                @php $seller2 = $top3[1]; $initial2 = strtoupper(substr($seller2['name'], 0, 1)); @endphp
                <div class="relative z-20 podium-card podium-second">
                    <div class="relative w-56 h-56 flex items-center justify-center">
                        <div class="absolute inset-0 bg-gradient-to-b from-cyan-400 via-cyan-500 to-cyan-600 opacity-20 blur-2xl shadow-cyan-500/50 shadow-2xl"></div>
                        <svg viewBox="0 0 200 240" class="w-full h-full">
                            <defs>
                                <linearGradient id="gradient-2" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color: rgb(37 99 235)"></stop>
                                    <stop offset="50%" style="stop-color: rgb(59 130 246)"></stop>
                                    <stop offset="100%" style="stop-color: rgb(29 78 216)"></stop>
                                </linearGradient>
                            </defs>
                            <path d="M 100 10 L 170 40 L 170 120 Q 170 180 100 230 Q 30 180 30 120 L 30 40 Z" fill="url(#gradient-2)" stroke="rgb(34 211 238)" stroke-width="3" class="drop-shadow-2xl"></path>
                            <path d="M 100 25 L 160 50 L 160 115 Q 160 165 100 210 Q 40 165 40 115 L 40 50 Z" fill="rgba(0,0,0,0.3)"></path>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-cyan-400 mb-3">
                                <div class="w-full h-full bg-gradient-to-br from-slate-700 to-slate-800 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-white">{{ $initial2 }}</span>
                                </div>
                            </div>
                            <h3 class="text-white font-bold text-lg text-center px-4 mb-1">{{ $seller2['name'] }}</h3>
                            <p class="text-white/90 font-semibold text-sm">{{ number_format($seller2['points'], 0, ',', '.') }} Pontos</p>
                            <div class="absolute -bottom-4 w-10 h-10 rounded-full bg-gradient-to-br from-cyan-400 via-cyan-500 to-cyan-600 border-4 border-slate-900 flex items-center justify-center shadow-cyan-500/50 shadow-xl">
                                <span class="text-white font-bold text-lg">2</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            @if(isset($top3[0]))
                @php $seller1 = $top3[0]; $initial1 = strtoupper(substr($seller1['name'], 0, 1)); @endphp
                <div class="relative z-30 podium-card podium-first" style="transform: scale(1.2);">
                    <div class="relative w-64 h-64 flex items-center justify-center">
                        <div class="absolute inset-0 bg-gradient-to-b from-yellow-400 via-amber-500 to-yellow-600 opacity-20 blur-2xl shadow-yellow-500/50 shadow-2xl"></div>
                        <svg viewBox="0 0 200 240" class="w-full h-full">
                            <defs>
                                <linearGradient id="gradient-1" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color: rgb(37 99 235)"></stop>
                                    <stop offset="50%" style="stop-color: rgb(59 130 246)"></stop>
                                    <stop offset="100%" style="stop-color: rgb(29 78 216)"></stop>
                                </linearGradient>
                            </defs>
                            <path d="M 100 10 L 170 40 L 170 120 Q 170 180 100 230 Q 30 180 30 120 L 30 40 Z" fill="url(#gradient-1)" stroke="rgb(250 204 21)" stroke-width="3" class="drop-shadow-2xl"></path>
                            <path d="M 100 25 L 160 50 L 160 115 Q 160 165 100 210 Q 40 165 40 115 L 40 50 Z" fill="rgba(0,0,0,0.3)"></path>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <div class="absolute -top-8">
                                <svg class="w-12 h-12 text-yellow-400 fill-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"></path>
                                    <path d="M5 21h14"></path>
                                </svg>
                            </div>
                            <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-yellow-400 mb-3">
                                <div class="w-full h-full bg-gradient-to-br from-slate-700 to-slate-800 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-white">{{ $initial1 }}</span>
                                </div>
                            </div>
                            <h3 class="text-white font-bold text-lg text-center px-4 mb-1">{{ $seller1['name'] }}</h3>
                            <p class="text-white/90 font-semibold text-sm">{{ number_format($seller1['points'], 0, ',', '.') }} Pontos</p>
                            <div class="absolute -bottom-4 w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 via-amber-500 to-yellow-600 border-4 border-slate-900 flex items-center justify-center shadow-yellow-500/50 shadow-xl">
                                <span class="text-white font-bold text-lg">1</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            @if(isset($top3[2]))
                @php $seller3 = $top3[2]; $initial3 = strtoupper(substr($seller3['name'], 0, 1)); @endphp
                <div class="relative z-20 podium-card podium-third">
                    <div class="relative w-56 h-56 flex items-center justify-center">
                        <div class="absolute inset-0 bg-gradient-to-b from-orange-400 via-orange-500 to-red-500 opacity-20 blur-2xl shadow-orange-500/50 shadow-2xl"></div>
                        <svg viewBox="0 0 200 240" class="w-full h-full">
                            <defs>
                                <linearGradient id="gradient-3" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color: rgb(37 99 235)"></stop>
                                    <stop offset="50%" style="stop-color: rgb(59 130 246)"></stop>
                                    <stop offset="100%" style="stop-color: rgb(29 78 216)"></stop>
                                </linearGradient>
                            </defs>
                            <path d="M 100 10 L 170 40 L 170 120 Q 170 180 100 230 Q 30 180 30 120 L 30 40 Z" fill="url(#gradient-3)" stroke="rgb(251 146 60)" stroke-width="3" class="drop-shadow-2xl"></path>
                            <path d="M 100 25 L 160 50 L 160 115 Q 160 165 100 210 Q 40 165 40 115 L 40 50 Z" fill="rgba(0,0,0,0.3)"></path>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-orange-400 mb-3">
                                <div class="w-full h-full bg-gradient-to-br from-slate-700 to-slate-800 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-white">{{ $initial3 }}</span>
                                </div>
                            </div>
                            <h3 class="text-white font-bold text-lg text-center px-4 mb-1">{{ $seller3['name'] }}</h3>
                            <p class="text-white/90 font-semibold text-sm">{{ number_format($seller3['points'], 0, ',', '.') }} Pontos</p>
                            <div class="absolute -bottom-4 w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 via-orange-500 to-red-500 border-4 border-slate-900 flex items-center justify-center shadow-orange-500/50 shadow-xl">
                                <span class="text-white font-bold text-lg">3</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@else
    <div class="text-center py-16 text-slate-400">
        <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
            <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
            <path d="M4 22h16"></path>
            <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
            <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
            <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
        </svg>
        <p class="text-lg">Nenhum participante ainda</p>
        <p class="text-sm mt-2">Comece cadastrando participantes</p>
    </div>
@endif
