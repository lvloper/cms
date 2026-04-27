<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class DeployCommand extends Command
{
    protected $signature = 'deploy {--no-pull} {--no-migrate} {--no-composer}';
    protected $description = 'Run deployment commands';

    public function __construct()
    {
        parent::__construct();
        $this->configureLogging();
    }

    protected function configureLogging()
    {
        $logPath = storage_path('logs/deploy.log');
        config(['logging.channels.deploy' => [
            'driver' => 'single',
            'path' => $logPath,
            'level' => 'debug',
        ]]);
    }

    public function handle()
    {
        if (!$this->option('no-pull')) {
            $this->info('Pulling latest changes from git...');
            $process = new Process(['git', 'pull']);
            $process->setWorkingDirectory($_SERVER['root'] ?? base_path());
            $process->setTimeout(null); // Prevent timeout for long git operations
            // Disable TTY completely as it causes issues on Windows
            $process->setTty(false);

            $process->run(function ($type, $buffer) {
                $timestamp = now()->format('Y-m-d H:i:s');
                if (Process::ERR === $type) {
                    $this->error($buffer);
                    Log::channel('deploy')->error("[{$timestamp}] " . $buffer);
                } else {
                    $this->line($buffer);
                    Log::channel('deploy')->info("[{$timestamp}] " . $buffer);
                }
            });

            if (!$process->isSuccessful()) {
                $this->error('Failed to pull from git.');
                $this->error($process->getErrorOutput());
                return 1;
            }
        }

        if (!$this->option('no-composer')) {
            $this->info('Installing composer dependencies...');
            $process = new Process(['composer', 'install', '--no-dev', '--optimize-autoloader']);
            $process->setWorkingDirectory($_SERVER['root'] ?? base_path());
            $process->setTimeout(null); // Prevent timeout for long-running composer install
            // Disable TTY completely as it causes issues on Windows
            $process->setTty(false);

            $process->run(function ($type, $buffer) {
                $timestamp = now()->format('Y-m-d H:i:s');
                if (Process::ERR === $type) {
                    $this->error($buffer);
                    Log::channel('deploy')->error("[{$timestamp}] " . $buffer);
                } else {
                    $this->line($buffer);
                    Log::channel('deploy')->info("[{$timestamp}] " . $buffer);
                }
            });

            if (!$process->isSuccessful()) {
                $this->error('Failed to install composer dependencies.');
                return 1;
            } else {
                $this->info('Composer dependencies installed successfully.');
            }
        }


        // $this->info('Restarting queue...');
        // Artisan::call('queue:restart');

        // $this->clearCache();

        if (!$this->option('no-migrate')) {
            $this->info('Running migrations...');
            $process = new Process(['php', 'artisan', 'migrate', '--force']);
            $process->setWorkingDirectory($_SERVER['root'] ?? base_path());
            $process->run();
        }

        try {
            $this->optimize();

            $this->info('Deployment completed successfully.');
            Log::channel('deploy')->info('[' . now()->format('Y-m-d H:i:s') . '] Deployment completed successfully');

            return 0;
        } catch (\Exception $e) {
            $this->error('Error during deployment: ' . $e->getMessage());
            Log::channel('deploy')->error('[' . now()->format('Y-m-d H:i:s') . '] Deployment failed: ' . $e->getMessage());
            return 1;
        }
    }

    public function clearCache()
    {
        $this->info('Clearing cache...');
        Artisan::call('optimize:clear');
        Artisan::call('filament:optimize-clear');
    }

    public function optimize()
    {
        $this->info('Optimizing...');

        $this->info('Clearing caches...');
        $this->runArtisanProcess(['php', 'artisan', 'optimize:clear']);
        $this->runArtisanProcess(['php', 'artisan', 'filament:optimize-clear']);

        $this->info('Building caches...');
        $this->runArtisanProcess(['php', 'artisan', 'optimize']);

        $this->info('Publishing Filament assets...');
        $this->runArtisanProcess(['php', 'artisan', 'filament:assets']);

        $this->info('Optimizing Filament...');
        $this->runArtisanProcess(['php', 'artisan', 'filament:optimize']);

        $this->info('Caching Filament components...');
        $this->runArtisanProcess(['php', 'artisan', 'filament:cache-components']);
    }

    protected function runArtisanProcess(array $command)
    {
        $process = new Process($command);
        $process->setWorkingDirectory($_SERVER['root'] ?? base_path());
        $process->run();

        $output = $process->getOutput();
        $errorOutput = $process->getErrorOutput();

        if (!$process->isSuccessful()) {
            $this->error("Error en: " . implode(' ', $command));
            $this->error($errorOutput);
            Log::channel('deploy')->error('Error en "' . implode(' ', $command) . '":', ['output' => $errorOutput]);
        } else {
            $this->line(trim($output));
            Log::channel('deploy')->info('Respuesta de "' . implode(' ', $command) . '":', ['output' => $output]);
        }
    }
}
