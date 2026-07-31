<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Paco\Campaign;
use App\Services\Paco\PacoPrefillService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

final class CreatePacoPrefillLinkCommand extends Command
{
    protected $signature = 'paco:prefill-link
        {campaign : Código de una campaña activa}
        {--name= : Nombre de la persona}
        {--email= : Email de contacto}
        {--phone= : WhatsApp de contacto}
        {--query= : Consulta inicial}
        {--source= : UTM source}
        {--medium= : UTM medium}
        {--utm-campaign= : UTM campaign}';

    protected $description = 'Genera un enlace de conversación con precarga privada y de un solo uso';

    public function handle(PacoPrefillService $prefills): int
    {
        $campaign = Campaign::query()->where('code', (string) $this->argument('campaign'))->first();

        if (! $campaign) {
            $this->error('No existe una campaña con ese código.');

            return self::FAILURE;
        }

        $email = $this->stringOption('email');
        $phone = $this->stringOption('phone');

        try {
            $link = $prefills->createLink($campaign, [
                'name' => $this->stringOption('name'),
                'email' => $email,
                'phone' => $phone,
                'contact_channel' => $phone ? 'whatsapp' : ($email ? 'email' : null),
                'initial_query' => $this->stringOption('query'),
            ], [
                'utm_source' => $this->stringOption('source'),
                'utm_medium' => $this->stringOption('medium'),
                'utm_campaign' => $this->stringOption('utm-campaign'),
            ]);
        } catch (ValidationException $exception) {
            $this->error(collect($exception->errors())->flatten()->join(' '));

            return self::FAILURE;
        }

        $this->newLine();
        $this->line($link);
        $this->newLine();
        $this->warn('El enlace vence y su precarga se consume una sola vez. No lo publiques en lugares abiertos.');

        return self::SUCCESS;
    }

    private function stringOption(string $name): ?string
    {
        $value = trim((string) $this->option($name));

        return $value !== '' ? $value : null;
    }
}
