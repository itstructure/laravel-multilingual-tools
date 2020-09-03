<?php

namespace Itstructure\Mult;

use Illuminate\Support\ServiceProvider;
use Itstructure\Mult\Commands\{PublishCommand, DatabaseCommand};

/**
 * Class MultServiceProvider
 *
 * @package Itstructure\Mult
 *
 * @author Andrey Girnik <girnikandrey@gmail.com>
 */
class MultServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerCommands();
    }

    public function boot()
    {
        // Loading settings
        $this->loadMigrations();

        // Publish settings
        $this->publishMigrations();
        $this->publishSeeds();
    }


    /*
    |--------------------------------------------------------------------------
    | COMMAND SETTINGS
    |--------------------------------------------------------------------------
    */

    /**
     * Register commands.
     * @return void
     */
    private function registerCommands(): void
    {
        $this->commands(Commands\PublishCommand::class);
        $this->commands(Commands\DatabaseCommand::class);
    }


    /*
    |--------------------------------------------------------------------------
    | LOADING SETTINGS
    |--------------------------------------------------------------------------
    */

    /**
     * Set directory to load migrations.
     * @return void
     */
    private function loadMigrations(): void
    {
        $this->loadMigrationsFrom($this->packagePath('database/migrations'));
    }


    /*
    |--------------------------------------------------------------------------
    | PUBLISH SETTINGS
    |--------------------------------------------------------------------------
    */

    /**
     * Publish migrations.
     * @return void
     */
    private function publishMigrations(): void
    {
        $this->publishes([
            $this->packagePath('database/migrations') => database_path('migrations'),
        ], 'migrations');
    }

    /**
     * Publish seeds.
     * @return void
     */
    private function publishSeeds(): void
    {
        $this->publishes([
            $this->packagePath('database/seeds') => database_path('seeds'),
        ], 'seeds');
    }


    /*
    |--------------------------------------------------------------------------
    | OTHER SETTINGS
    |--------------------------------------------------------------------------
    */

    /**
     * Get package path.
     * @param $path
     * @return string
     */
    private function packagePath($path): string
    {
        return __DIR__ . "/../" . $path;
    }
}
