<?php

namespace ArgentCrusade\Stronghold\Providers;

use ArgentCrusade\Stronghold\Contracts\OneTimeTokensRepositoryInterface;
use ArgentCrusade\Stronghold\OneTimeTokensRepository;
use Illuminate\Support\ServiceProvider;

class StrongholdServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(
            realpath(__DIR__.'/../../database/migrations')
        );

        $configPath = realpath(__DIR__.'/../../config/stronghold.php');

        $this->publishes([
            $configPath => config_path('stronghold.php')
        ], 'config');

        $this->mergeConfigFrom($configPath, 'stronghold');
    }

    public function register()
    {
        $this->app->bind(
            OneTimeTokensRepositoryInterface::class,
            config('stronghold.tokens_repository', OneTimeTokensRepository::class)
        );
    }
}
