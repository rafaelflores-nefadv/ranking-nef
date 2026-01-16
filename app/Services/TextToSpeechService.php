<?php

namespace App\Services;

use App\Models\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class TextToSpeechService
{
    public function speak(string $text): void
    {
        $text = trim($text);

        if ($text === '') {
            return;
        }

        try {
            $voiceName = $this->getVoiceName();
            $this->runTts($text, $voiceName);
        } catch (\Throwable $e) {
            Log::warning('Falha ao executar TTS', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function runTts(string $text, ?string $voiceName): void
    {
        $osFamily = PHP_OS_FAMILY;

        if ($osFamily === 'Windows') {
            $escaped = str_replace("'", "''", $text);
            $voiceCommand = '';
            if ($voiceName) {
                $voiceEscaped = str_replace("'", "''", $voiceName);
                $voiceCommand = "\$speak.SelectVoice('{$voiceEscaped}'); ";
            }
            $command = "Add-Type -AssemblyName System.Speech; "
                . "\$speak = New-Object System.Speech.Synthesis.SpeechSynthesizer; "
                . $voiceCommand
                . "\$speak.Speak('{$escaped}');";
            $process = new Process(['powershell', '-NoProfile', '-Command', $command]);
            $process->setTimeout(30);
            $process->run();
            return;
        }

        if ($osFamily === 'Darwin') {
            $command = ['say'];
            if ($voiceName) {
                $command[] = '-v';
                $command[] = $voiceName;
            }
            $command[] = $text;
            $process = new Process($command);
            $process->setTimeout(30);
            $process->run();
            return;
        }

        $command = ['espeak'];
        if ($voiceName) {
            $command[] = '-v';
            $command[] = $voiceName;
        }
        $command[] = $text;
        $process = new Process($command);
        $process->setTimeout(30);
        $process->run();
    }

    private function getVoiceName(): ?string
    {
        $value = Config::where('key', 'notifications_voice_name')->value('value');

        if (!$value) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
