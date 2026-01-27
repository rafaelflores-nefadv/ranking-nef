<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sector::updateOrCreate(
            ['slug' => 'geral'],
            [
                'name' => 'Geral',
                'description' => 'Setor padrão.',
                'is_active' => true,
            ]
        );
    }
}
