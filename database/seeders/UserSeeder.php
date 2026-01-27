<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Sector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSectorId = Sector::where('slug', 'geral')->value('id');

        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@nef.local',
                'role' => 'admin',
                'sector_id' => null,
            ],
            [
                'name' => 'Supervisor',
                'email' => 'supervisor@nef.local',
                'role' => 'supervisor',
                'sector_id' => $defaultSectorId,
            ],
            [
                'name' => 'Usuário',
                'email' => 'user@nef.local',
                'role' => 'user',
                'sector_id' => $defaultSectorId,
            ],
        ];

        $createdUsers = [];
        $updatedUsers = [];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'role' => $userData['role'],
                    'sector_id' => $userData['sector_id'],
                ]
            );

            if ($user->wasRecentlyCreated) {
                $createdUsers[] = $userData;
            } else {
                $updatedUsers[] = $userData;
            }
        }

        if ($this->command) {
            $this->command->info('Usuários para login (senha padrão: password):');

            foreach ($users as $userData) {
                $this->command->line(" - {$userData['email']} ({$userData['role']})");
            }

            if (!empty($createdUsers)) {
                $this->command->info('Criados nesta execução: ' . count($createdUsers));
            }

            if (!empty($updatedUsers)) {
                $this->command->info('Já existiam/atualizados: ' . count($updatedUsers));
            }
        }
    }
}
