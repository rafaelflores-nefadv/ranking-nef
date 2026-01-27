<?php

namespace App\Providers;

use App\Models\Config;
use App\Services\SectorService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $saleTerm = Config::where('key', 'sale_term')->value('value') ?: 'Venda';
            $view->with('saleTerm', $saleTerm);
            $view->with('saleTermLower', strtolower($saleTerm));

            $user = auth()->user();
            if ($user && $user->role === 'admin') {
                $sectorService = app(SectorService::class);
                $currentSectorId = $sectorService->resolveSectorIdForRequest(request());
                $sectorOptions = $sectorService->getActiveSectors();
                $view->with('sectorOptions', $sectorOptions);
                $view->with('currentSectorId', $currentSectorId);
            }
        });
    }
}
