<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class PacoLogs extends Command
{
    protected $signature = 'paco:logs
        {--follow : Seguir nuevos eventos en tiempo real}
        {--conversation= : Filtrar por ID de conversación}
        {--lines=50 : Cantidad de líneas iniciales}';

    protected $description = 'Leer la traza de desarrollo de Paco';

    public function handle(): int
    {
        $path = storage_path('logs/paco.log');
        $conversation = $this->option('conversation');
        $lines = max(1, (int) $this->option('lines'));

        if (! is_file($path)) {
            $this->warn("Todavía no existe {$path}. Ejecutá una conversación de Paco primero.");

            return self::SUCCESS;
        }

        $this->stream($path, $lines, is_string($conversation) ? $conversation : null);

        return self::SUCCESS;
    }

    private function stream(string $path, int $lines, ?string $conversation): void
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $this->error('No se pudo abrir el archivo de logs.');

            return;
        }

        $content = stream_get_contents($handle) ?: '';
        $initialLines = array_slice(preg_split('/\R/', trim($content)) ?: [], -$lines);
        foreach ($initialLines as $line) {
            $this->writeIfMatches($line, $conversation);
        }

        if (! $this->option('follow')) {
            fclose($handle);

            return;
        }

        fseek($handle, 0, SEEK_END);
        $this->info('Siguiendo logs de Paco. Presioná Ctrl+C para salir.');
        while (true) {
            $line = fgets($handle);
            if ($line !== false) {
                $this->writeIfMatches(trim($line), $conversation);

                continue;
            }

            clearstatcache(true, $path);
            usleep(250000);
        }
    }

    private function writeIfMatches(string $line, ?string $conversation): void
    {
        if ($line === '' || ($conversation !== null && ! Str::contains($line, $conversation))) {
            return;
        }

        $this->line($line);
    }
}
