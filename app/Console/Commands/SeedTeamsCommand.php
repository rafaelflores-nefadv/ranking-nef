<?php

namespace App\Console\Commands;

use Database\Seeders\TeamSeeder;
use Illuminate\Console\Command;

class SeedTeamsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:teams {--quantity=6 : Quantidade de equipes a criar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria equipes no banco de dados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $quantity = (int) $this->option('quantity');
        
        if ($quantity < 1) {
            $this->error('A quantidade deve ser maior que 0!');
            return 1;
        }

        $seeder = new TeamSeeder();
        $seeder->setCommand($this);
        $seeder->setQuantity($quantity);
        $seeder->run();

        return 0;
    }
}
