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
        $this->publishSeeders();
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
    private function publishSeeders(): void
    {
        $this->publishes([
            $this->packagePath('database/seeders') => database_path('seeders'),
        ], 'seeders');
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
