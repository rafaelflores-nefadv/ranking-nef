<?php

namespace Database\Seeders;

use App\Models\Config;
use App\Services\PermissionService;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            [
                'key' => 'auto_process_occurrences',
                'value' => 'true',
            ],
            [
                'key' => 'points_precision',
                'value' => '2',
            ],
            [
                'key' => 'ranking_limit',
                'value' => '100',
            ],
            [
                'key' => 'sale_term',
                'value' => 'Venda',
            ],
            [
                'key' => 'season_required',
                'value' => 'false',
            ],
            [
                'key' => 'season_duration_days',
                'value' => '365',
            ],
            [
                'key' => 'season_auto_renew',
                'value' => 'true',
            ],
            [
                'key' => 'notifications_system_enabled',
                'value' => 'true',
            ],
            [
                'key' => 'notifications_email_enabled',
                'value' => 'true',
            ],
            [
                'key' => 'notifications_sound_enabled',
                'value' => 'true',
            ],
            [
                'key' => 'notifications_popup_max_count',
                'value' => '2',
            ],
            [
                'key' => 'notifications_popup_auto_close_seconds',
                'value' => '7',
            ],
            [
                'key' => 'notifications_voice_enabled',
                'value' => 'false',
            ],
            [
                'key' => 'notifications_voice_mode',
                'value' => 'server',
            ],
            [
                'key' => 'notifications_voice_scope',
                'value' => 'global',
            ],
            [
                'key' => 'notifications_voice_interval_minutes',
                'value' => '15',
            ],
            [
                'key' => 'notifications_voice_only_when_changed',
                'value' => 'false',
            ],
            [
                'key' => 'notifications_voice_name',
                'value' => '',
            ],
            [
                'key' => 'notifications_voice_browser_name',
                'value' => '',
            ],
            [
                'key' => 'notifications_events_config',
                'value' => json_encode([
                    'sale_registered' => [
                        'system' => true,
                        'email' => false,
                        'sound' => true,
                    ],
                    'ranking_position_changed' => [
                        'system' => true,
                        'email' => false,
                        'sound' => false,
                    ],
                    'entered_top_3' => [
                        'system' => true,
                        'email' => true,
                        'sound' => true,
                    ],
                    'goal_reached' => [
                        'system' => true,
                        'email' => true,
                        'sound' => true,
                    ],
                    'season_started' => [
                        'system' => true,
                        'email' => true,
                        'sound' => false,
                    ],
                    'season_ended' => [
                        'system' => true,
                        'email' => true,
                        'sound' => false,
                    ],
                ]),
            ],
            [
                'key' => 'supervisor_permissions',
                'value' => json_encode(PermissionService::getDefaultPermissions()),
            ],
            [
                'key' => 'monitor_theme',
                'value' => 'default',
            ],
        ];

        foreach ($configs as $configData) {
            Config::updateOrCreate(
                ['key' => $configData['key']],
                ['value' => $configData['value']]
            );
        }
    }
}
