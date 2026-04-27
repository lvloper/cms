<?php

namespace App\Console\Commands;

use App\Filament\Exports\RecurseroExporter;
use App\Models\Recursero;
use Illuminate\Console\Command;
use Filament\Actions\Exports\Models\Export;

class TestRecurseroExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recursero:test-export {--limit=5 : Limit number of records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test RecurseroExporter with ODS column names';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing RecurseroExporter with ODS format...');
        
        $limit = $this->option('limit');
        
        // Obtener algunos recurseros
        $recurseros = Recursero::take($limit)->get();
        
        if ($recurseros->isEmpty()) {
            $this->error('No recurseros found in database.');
            return 1;
        }
        
        $this->info("Found {$recurseros->count()} recurseros for export test");
        
        // Mostrar las columnas que se exportarían
        $columns = RecurseroExporter::getColumns();
        
        $this->info('Export columns (ODS format):');
        
        foreach ($columns as $column) {
            $fieldName = $column->getName();
            $label = $column->getLabel();
            $this->line("  - {$fieldName} → '{$label}'");
        }
        
        // Mostrar datos de ejemplo
        $this->newLine();
        $this->info('Sample data preview:');
        
        foreach ($recurseros as $recursero) {
            $this->line("Recursero: {$recursero->title}");
            $this->line("  Tipo: {$recursero->establishment_type}");
            $this->line("  Calle: {$recursero->address}");
            $this->line("  Partido: {$recursero->district}");
            $this->line("  Profesional: {$recursero->professional_name}");
            $this->newLine();
            break; // Solo mostrar el primero como ejemplo
        }
        
        $this->info('✅ RecurseroExporter is ready with ODS column format!');
        $this->info('You can now export from Filament admin panel.');
        
        return 0;
    }
}
