<div class="w-full">
    <div class="flex items-center justify-between text-sm mb-2">
        <span class="text-slate-400">Progresso</span>
        <span class="text-white font-semibold">{{ number_format($progress, 1) }}%</span>
    </div>
    <div class="w-full bg-slate-800 rounded-full {{ $getHeightClass() }}">
        <div class="{{ $getHeightClass() }} rounded-full transition-all {{ $getColorClass() }} flex items-center justify-end pr-2"
            style="width: {{ $progress }}%">
            @if($progress >= 5)
            <span class="text-xs text-white font-semibold">{{ number_format($progress, 0) }}%</span>
            @endif
        </div>
    </div>
    <div class="mt-1 text-xs text-slate-500">
        {{ number_format($currentValue, 0, ',', '.') }} / {{ number_format($targetValue, 0, ',', '.') }} pontos
    </div>
</div>