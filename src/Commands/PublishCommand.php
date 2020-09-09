<?php

namespace Itstructure\Mult\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Itstructure\Mult\MultServiceProvider;

/**
 * Class PublishCommand
 *
 * @package Itstructure\Mult\Commands
 *
 * @author Andrey Girnik <girnikandrey@gmail.com>
 */
class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'mult:publish '.
    '{--force : Overwrite existing files by default. This option can not be used.}'.
    '{--only= : Publish only specific part. Available parts: migrations, seeders. This option can not be used.}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Publish the Mult package parts.';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $this->info('Starting publication process of the Mult package parts...');

        $callArguments = ['--provider' => MultServiceProvider::class];

        if ($this->option('only')) {
            switch ($this->option('only')) {
                case 'migrations':
                    $this->info('Publish just a part: migrations.');
                    $callArguments['--tag'] = 'migrations';
                    break;

                case 'seeders':
                    $this->info('Publish just a part: seeders.');
                    $callArguments['--tag'] = 'seeders';
                    break;

                default:
                    $this->error('Invalid "only" argument value!');
                    return;
            }

        } else {
            $this->info('Publish all parts: migrations, seeders.');
        }

        if ($this->option('force')) {
            $this->warn('Force publishing.');
            $callArguments['--force'] = true;
        }

        $this->call('vendor:publish', $callArguments);

        $this->info('Dumping the autoloaded files and reloading all new files.');
        $composer = $this->findComposer();
        $process = Process::fromShellCommandline($composer.' dump-autoload -o');
        $process->setTimeout(null);
        $process->setWorkingDirectory(base_path())->run();
    }

    /**
     * Get the composer command for the environment.
     * @return string
     */
    private function findComposer()
    {
        if (file_exists(getcwd().'/composer.phar')) {
            return '"'.PHP_BINARY.'" '.getcwd().'/composer.phar';
        }

        return 'composer';
    }
}
