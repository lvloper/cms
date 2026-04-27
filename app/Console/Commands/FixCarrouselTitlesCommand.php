<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Page;

class FixCarrouselTitlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:carrousel-titles
                            {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cambia el título de todos los bloques Carrousel a "Te puede interesar"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->info('🔍 Buscando bloques Carrousel en Páginas...');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('⚠️  Modo DRY-RUN: No se realizarán cambios');
            $this->newLine();
        }

        $pages = Page::all();
        $totalPages = 0;
        $totalBlocksChanged = 0;

        foreach ($pages as $page) {
            if (!$page->blocks || !is_iterable($page->blocks)) {
                continue;
            }

            $hasChanges = false;
            $blocks = $page->blocks->toArray();
            $changedBlocks = [];

            foreach ($blocks as $blockIndex => &$block) {
                // Check if this is a Carrousel block
                if (!isset($block['type']) || $block['type'] !== 'Carrousel') {
                    continue;
                }

                // Check if it has data.title
                if (!isset($block['data']['title'])) {
                    continue;
                }

                $currentTitle = $block['data']['title'];
                $newTitle = 'Te puede interesar';

                // Skip if already has the correct title
                if ($currentTitle === $newTitle) {
                    continue;
                }

                $changedBlocks[] = [
                    'blockIndex' => $blockIndex,
                    'oldTitle' => $currentTitle,
                    'newTitle' => $newTitle,
                ];

                // Update the title
                $block['data']['title'] = $newTitle;
                $hasChanges = true;
                $totalBlocksChanged++;
            }

            if ($hasChanges) {
                $totalPages++;
                $this->displayChanges($page, $changedBlocks, $isDryRun);

                if (!$isDryRun) {
                    $page->blocks = $blocks;
                    $page->save();
                }
            }
        }

        $this->newLine();

        if ($totalPages === 0) {
            $this->info('✅ No se encontraron bloques Carrousel que necesiten actualización.');
        } else {
            if ($isDryRun) {
                $this->warn("📊 Se encontraron {$totalBlocksChanged} bloques Carrousel en {$totalPages} páginas que necesitan actualización.");
                $this->comment('💡 Ejecuta sin --dry-run para aplicar los cambios.');
            } else {
                $this->info("✅ Se actualizaron {$totalBlocksChanged} bloques Carrousel en {$totalPages} páginas.");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Display changes for a page
     */
    protected function displayChanges(Page $page, array $changedBlocks, bool $isDryRun): void
    {
        $title = $page->route->title ?? $page->name ?? 'Sin título';
        $icon = $isDryRun ? '📝' : '✓';
        
        $this->line("  {$icon} Página: {$title}");
        
        foreach ($changedBlocks as $change) {
            $this->line("     Bloque #{$change['blockIndex']} (Carrousel)");
            $this->line("     • \"{$change['oldTitle']}\" → \"{$change['newTitle']}\"");
        }
        
        if (isset($page->route)) {
            $this->line("     🌐 " . url($page->route->getFullPath()));
        }
        
        $this->newLine();
    }
}
