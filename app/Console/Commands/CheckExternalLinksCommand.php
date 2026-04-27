<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Page;
use App\Models\Blog;
use App\Models\Jobsoffer;

class CheckExternalLinksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:external-links
                            {--fix : Automatically fix the issues by setting new_window to true}
                            {--model= : Check only specific model (page, blog, jobsoffer)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Busca enlaces externos en los contenidos que no abran en nueva pestaña';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Buscando enlaces externos sin nueva pestaña...');
        $this->newLine();

        $models = $this->getModelsToCheck();
        $totalIssues = 0;
        $totalFixed = 0;

        foreach ($models as $modelClass => $modelName) {
            $this->info("Revisando: {$modelName}");
            $results = $this->checkModel($modelClass, $modelName);
            $totalIssues += $results['issues'];
            $totalFixed += $results['fixed'];
        }

        $this->newLine();
        
        if ($totalIssues === 0) {
            $this->info('✅ No se encontraron enlaces externos sin nueva pestaña.');
        } else {
            $this->warn("⚠️  Total de problemas encontrados: {$totalIssues}");
            
            if ($this->option('fix')) {
                $this->info("✅ Se corrigieron {$totalFixed} enlaces.");
            } else {
                $this->comment('💡 Ejecuta el comando con --fix para corregir automáticamente.');
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Get models to check based on options
     */
    protected function getModelsToCheck(): array
    {
        $allModels = [
            Page::class => 'Páginas',
            Blog::class => 'Blogs',
            Jobsoffer::class => 'Ofertas Laborales',
        ];

        if ($modelOption = $this->option('model')) {
            $modelMap = [
                'page' => Page::class,
                'blog' => Blog::class,
                'jobsoffer' => Jobsoffer::class,
            ];

            if (!isset($modelMap[$modelOption])) {
                $this->error("Modelo inválido. Opciones: page, blog, jobsoffer");
                return [];
            }

            return [$modelMap[$modelOption] => $allModels[$modelMap[$modelOption]]];
        }

        return $allModels;
    }

    /**
     * Check a specific model for external links issues
     */
    protected function checkModel(string $modelClass, string $modelName): array
    {
        $records = $modelClass::all();
        $issuesCount = 0;
        $fixedCount = 0;

        foreach ($records as $record) {
            if (!$record->blocks || !is_iterable($record->blocks)) {
                continue;
            }

            $hasChanges = false;
            $blocks = $record->blocks->toArray();

            foreach ($blocks as $blockIndex => &$block) {
                $result = $this->checkBlock($block, $record, $blockIndex, $modelName);
                $issuesCount += $result['issues'];
                $fixedCount += $result['fixed'];
                
                if ($result['fixed'] > 0) {
                    $hasChanges = true;
                }
            }

            // Save changes if fix option is enabled
            if ($hasChanges && $this->option('fix')) {
                $record->blocks = $blocks;
                $record->save();
            }
        }

        if ($issuesCount > 0) {
            $this->line("  └─ Encontrados: {$issuesCount} problemas");
        } else {
            $this->line("  └─ ✓ Sin problemas");
        }

        return ['issues' => $issuesCount, 'fixed' => $fixedCount];
    }

    /**
     * Check a block for external links issues
     */
    protected function checkBlock(array &$block, $record, int $blockIndex, string $modelName): array
    {
        $issuesCount = 0;
        $fixedCount = 0;

        // Check all possible route/link fields in the block
        $linkFields = $this->findLinkFields($block);

        foreach ($linkFields as $path) {
            $linkData = $this->getNestedValue($block, $path);
            
            if (!is_array($linkData)) {
                continue;
            }

            // Check if it's an external link (route_id = '0') without new_window
            if ($this->isExternalLinkWithoutNewWindow($linkData)) {
                $issuesCount++;
                $this->displayIssue($record, $blockIndex, $path, $linkData);

                if ($this->option('fix')) {
                    $this->setNestedValue($block, $path . '.new_window', true);
                    $fixedCount++;
                }
            }
        }

        return ['issues' => $issuesCount, 'fixed' => $fixedCount];
    }

    /**
     * Find all link fields in a block recursively
     */
    protected function findLinkFields(array $data, string $prefix = ''): array
    {
        $linkFields = [];

        foreach ($data as $key => $value) {
            $currentPath = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                // Check if this looks like a route picker structure
                // Could have route_id (new format) or just external_url (old format)
                if (isset($value['route_id']) || isset($value['external_url']) || isset($value['new_window'])) {
                    $linkFields[] = $currentPath;
                } else {
                    // Recurse into nested arrays
                    $linkFields = array_merge($linkFields, $this->findLinkFields($value, $currentPath));
                }
            }
        }

        return $linkFields;
    }

    /**
     * Check if link data represents an external link without new_window
     */
    protected function isExternalLinkWithoutNewWindow(array $linkData): bool
    {
        // Check if external_url exists (this is the main indicator of external link)
        if (empty($linkData['external_url'])) {
            return false;
        }

        // Validate it's actually an external URL (starts with http:// or https://)
        $url = $linkData['external_url'];
        if (!preg_match('/^https?:\/\//i', $url)) {
            return false;
        }

        // Check if new_window is false or not set
        // new_window should be true for external links
        return empty($linkData['new_window']) || $linkData['new_window'] === false;
    }

    /**
     * Display issue information
     */
    protected function displayIssue($record, int $blockIndex, string $path, array $linkData): void
    {
        $title = $record->route->title ?? $record->title ?? 'Sin título';
        $url = $linkData['external_url'] ?? 'N/A';
        
        $this->line("  ");
        $this->warn("  ⚠️  Problema encontrado:");
        $this->line("     📄 Registro: {$title}");
        $this->line("     🔗 URL Externa: {$url}");
        $this->line("     📍 Bloque #{$blockIndex} → {$path}");
        
        if (isset($record->route)) {
            $this->line("     🌐 Ver: " . url($record->route->getFullPath()));
        }
    }

    /**
     * Get nested value from array using dot notation
     */
    protected function getNestedValue(array $array, string $path)
    {
        $keys = explode('.', $path);
        $value = $array;

        foreach ($keys as $key) {
            if (!is_array($value) || !isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * Set nested value in array using dot notation
     */
    protected function setNestedValue(array &$array, string $path, $value): void
    {
        $keys = explode('.', $path);
        $current = &$array;

        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }

        $current = $value;
    }
}
