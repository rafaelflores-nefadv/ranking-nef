<?php

namespace App\Providers;

use App\Models\Config;
use App\Models\ScoreRule;
use App\Models\Season;
use App\Models\Seller;
use App\Models\Team;
use App\Models\User;
use App\Policies\ConfigPolicy;
use App\Policies\ScoreRulePolicy;
use App\Policies\SeasonPolicy;
use App\Policies\SellerPolicy;
use App\Policies\TeamPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Seller::class => SellerPolicy::class,
        Team::class => TeamPolicy::class,
        Season::class => SeasonPolicy::class,
        ScoreRule::class => ScoreRulePolicy::class,
        Config::class => ConfigPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
