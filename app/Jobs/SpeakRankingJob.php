<?php

namespace App\Jobs;

use App\Models\Config;
use App\Models\NotificationHistory;
use App\Services\TextToSpeechService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SpeakRankingJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $scope,
        private string $content
    ) {
    }

    public function handle(TextToSpeechService $textToSpeechService): void
    {
        $mode = Config::where('key', 'notifications_voice_mode')->value('value') ?? 'server';
        $mode = $mode ?: 'server';

        if (in_array($mode, ['server', 'both'], true)) {
            $textToSpeechService->speak($this->content);
        }

        NotificationHistory::create([
            'type' => 'voice_ranking',
            'scope' => $this->scope,
            'content' => $this->content,
        ]);
    }
}
