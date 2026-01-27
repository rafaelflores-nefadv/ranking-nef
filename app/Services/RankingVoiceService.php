<?php

namespace App\Services;

use App\Jobs\SpeakRankingJob;
use App\Models\Config;
use App\Models\Sector;
use App\Models\Season;
use App\Models\Seller;
use App\Models\Team;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class RankingVoiceService
{
    private const DEFAULT_INTERVAL_MINUTES = 15;

    public function dispatchIfDue(): void
    {
        if (!$this->getBoolConfig('notifications_voice_enabled', false)) {
            return;
        }

        $interval = $this->getIntConfig('notifications_voice_interval_minutes', self::DEFAULT_INTERVAL_MINUTES);
        $sectors = Sector::where('is_active', true)->get(['id', 'name']);

        $season = Season::where('is_active', true)->first();
        if (!$season) {
            Log::info('Leitura por voz ignorada: nenhuma temporada ativa.');
            return;
        }

        $scope = $this->getStringConfig('notifications_voice_scope', 'global');
        $onlyWhenChanged = $this->getBoolConfig('notifications_voice_only_when_changed', false);
        $precision = $this->getIntConfig('points_precision', 2);

        foreach ($sectors as $sector) {
            $sectorKey = $sector->id;
            $lastRunAt = $this->getDateConfig($this->sectorKey('notifications_voice_last_run_at', $sectorKey));

            if ($lastRunAt && now()->diffInMinutes($lastRunAt) < $interval) {
                continue;
            }

            $dispatched = false;

            if (in_array($scope, ['global', 'both'], true)) {
                $globalTop = $this->getTopSellers($season->id, null, $sectorKey);
                if ($globalTop->isNotEmpty()) {
                    $hash = $this->hashRanking($globalTop);
                    $lastHash = $this->getStringConfig($this->sectorKey('notifications_voice_last_hash_global', $sectorKey), '');

                    if (!$onlyWhenChanged || $hash !== $lastHash) {
                        $content = $this->buildRankingText(
                            'Top 3 do ranking geral:',
                            $globalTop->all(),
                            $precision
                        );
                        SpeakRankingJob::dispatch('global', $content, $sectorKey);
                        $this->setConfig($this->sectorKey('notifications_voice_last_hash_global', $sectorKey), $hash);
                        $dispatched = true;
                    }
                }
            }

            if (in_array($scope, ['teams', 'both'], true)) {
                $lastHashes = $this->getJsonConfig($this->sectorKey('notifications_voice_last_hash_teams', $sectorKey));
                $teams = Team::where('sector_id', $sectorKey)->orderBy('name')->get(['id', 'name']);

                foreach ($teams as $team) {
                    $teamTop = $this->getTopSellers($season->id, $team->id, $sectorKey);
                    if ($teamTop->isEmpty()) {
                        continue;
                    }

                    $hash = $this->hashRanking($teamTop);
                    $previousHash = $lastHashes[$team->id] ?? '';

                    if ($onlyWhenChanged && $hash === $previousHash) {
                        continue;
                    }

                    $content = $this->buildRankingText(
                        "Top 3 da equipe {$team->name}:",
                        $teamTop->all(),
                        $precision
                    );
                    SpeakRankingJob::dispatch('team', $content, $sectorKey);
                    $lastHashes[$team->id] = $hash;
                    $dispatched = true;
                }

                $this->setConfig($this->sectorKey('notifications_voice_last_hash_teams', $sectorKey), json_encode($lastHashes));
            }

            if ($dispatched) {
                $this->setConfig($this->sectorKey('notifications_voice_last_run_at', $sectorKey), now()->toIso8601String());
            }
        }
    }

    private function getTopSellers(string $seasonId, ?string $teamId, ?string $sectorId)
    {
        $query = Seller::query()
            ->where('season_id', $seasonId)
            ->orderBy('points', 'desc')
            ->limit(3);

        if ($sectorId) {
            $query->where('sector_id', $sectorId);
        }

        if ($teamId) {
            $query->whereHas('teams', function ($q) use ($teamId) {
                $q->where('teams.id', $teamId);
            });
        }

        return $query->get(['id', 'name', 'points']);
    }

    private function buildRankingText(string $title, array $entries, int $precision): string
    {
        $parts = [$title];

        foreach ($entries as $index => $seller) {
            $position = $index + 1;
            $points = number_format((float) $seller->points, $precision, ',', '.');
            $parts[] = "{$position}o lugar: {$seller->name}, {$points} pontos.";
        }

        return implode(' ', $parts);
    }

    private function hashRanking($collection): string
    {
        $payload = $collection->map(function ($seller, $index) {
            return "{$index}:{$seller->id}:{$seller->points}";
        })->implode('|');

        return sha1($payload);
    }

    private function getBoolConfig(string $key, bool $default): bool
    {
        $value = Config::where('key', $key)->value('value');

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function getStringConfig(string $key, string $default): string
    {
        $value = Config::where('key', $key)->value('value');

        if ($value === null || $value === '') {
            return $default;
        }

        return (string) $value;
    }

    private function getIntConfig(string $key, int $default): int
    {
        $value = Config::where('key', $key)->value('value');

        if ($value === null || $value === '') {
            return $default;
        }

        return (int) $value;
    }

    private function getDateConfig(string $key): ?Carbon
    {
        $value = Config::where('key', $key)->value('value');

        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function getJsonConfig(string $key): array
    {
        $value = Config::where('key', $key)->value('value');

        if (!$value) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function setConfig(string $key, string $value): void
    {
        Config::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    private function sectorKey(string $baseKey, string $sectorId): string
    {
        return "{$baseKey}_{$sectorId}";
    }
}
