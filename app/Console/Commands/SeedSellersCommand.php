<?php

namespace App\Console\Commands;

use Database\Seeders\SellerSeeder;
use Illuminate\Console\Command;

class SeedSellersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:sellers {--quantity=30 : Quantidade de vendedores a criar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria vendedores no banco de dados e os relaciona às equipes';

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

        $seeder = new SellerSeeder();
        $seeder->setCommand($this);
        $seeder->setQuantity($quantity);
        $seeder->run();

        return 0;
    }
}
