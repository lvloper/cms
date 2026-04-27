<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Blog;
use App\Models\Page;
use App\Models\Material;
use App\Models\Recursero;
use App\Models\Configuration;
use App\Models\Banner;
use Illuminate\Support\Facades\DB;

class FindDevDomainLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'domain:find-dev-links {--replace : Reemplazar automáticamente dev.huesped.org.ar por huesped.org.ar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Busca todos los enlaces a dev.huesped.org.ar en los contenidos de la base de datos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Buscando enlaces a dev.huesped.org.ar...');
        $this->newLine();

        $replace = $this->option('replace');
        $totalFound = 0;
        $totalReplaced = 0;

        // Definir los modelos y campos a revisar
        $modelsToCheck = [
            [
                'model' => Blog::class,
                'name' => 'Blogs',
                'fields' => ['title', 'description', 'content', 'blocks']
            ],
            [
                'model' => Page::class,
                'name' => 'Pages',
                'fields' => ['title', 'blocks']
            ],
            [
                'model' => Material::class,
                'name' => 'Materials',
                'fields' => ['title', 'description', 'content', 'blocks']
            ],
            [
                'model' => Recursero::class,
                'name' => 'Recurseros',
                'fields' => ['title', 'description', 'content', 'blocks']
            ],
            [
                'model' => Banner::class,
                'name' => 'Banners',
                'fields' => ['title', 'description', 'blocks']
            ],
        ];

        foreach ($modelsToCheck as $modelConfig) {
            $modelClass = $modelConfig['model'];
            $modelName = $modelConfig['name'];
            $fields = $modelConfig['fields'];

            $this->info("📋 Revisando: {$modelName}");

            $found = [];
            
            // Buscar en cada campo
            foreach ($fields as $field) {
                $query = $modelClass::query();
                
                // Verificar si el campo existe en la tabla
                try {
                    $records = $query->where($field, 'like', '%dev.huesped.org.ar%')
                        ->orWhere($field, 'like', '%dev\.huesped\.org\.ar%')
                        ->get();

                    foreach ($records as $record) {
                        $fieldValue = $record->$field;
                        
                        // Contar ocurrencias
                        if (is_string($fieldValue)) {
                            $count = substr_count($fieldValue, 'dev.huesped.org.ar');
                        } elseif (is_array($fieldValue)) {
                            $count = substr_count(json_encode($fieldValue), 'dev.huesped.org.ar');
                        } else {
                            continue;
                        }

                        if ($count > 0) {
                            $key = $record->id . '-' . $field;
                            if (!isset($found[$key])) {
                                $found[$key] = [
                                    'id' => $record->id,
                                    'field' => $field,
                                    'title' => $record->title ?? $record->name ?? "ID: {$record->id}",
                                    'count' => $count,
                                    'record' => $record
                                ];
                                $totalFound += $count;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Campo no existe en este modelo, continuar
                    continue;
                }
            }

            if (count($found) > 0) {
                $this->table(
                    ['ID', 'Campo', 'Título/Nombre', 'Ocurrencias'],
                    collect($found)->map(function ($item) {
                        return [
                            $item['id'],
                            $item['field'],
                            \Illuminate\Support\Str::limit($item['title'], 50),
                            $item['count']
                        ];
                    })->toArray()
                );

                // Si se activó el reemplazo
                if ($replace) {
                    foreach ($found as $item) {
                        $record = $item['record'];
                        $field = $item['field'];
                        $fieldValue = $record->$field;

                        if (is_string($fieldValue)) {
                            $record->$field = str_replace('dev.huesped.org.ar', 'huesped.org.ar', $fieldValue);
                            $record->save();
                            $totalReplaced += $item['count'];
                        } elseif (is_array($fieldValue)) {
                            $jsonString = json_encode($fieldValue);
                            $jsonString = str_replace('dev.huesped.org.ar', 'huesped.org.ar', $jsonString);
                            $record->$field = json_decode($jsonString, true);
                            $record->save();
                            $totalReplaced += $item['count'];
                        }
                    }
                    $this->info("✅ Reemplazados {$totalReplaced} enlaces en {$modelName}");
                }
            } else {
                $this->line("   ✓ No se encontraron enlaces en {$modelName}");
            }

            $this->newLine();
        }

        // Resumen final
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("📊 RESUMEN:");
        $this->info("   Total de enlaces encontrados: {$totalFound}");
        
        if ($replace) {
            $this->info("   Total de enlaces reemplazados: {$totalReplaced}");
            $this->newLine();
            $this->warn('⚠️  IMPORTANTE: Los cambios ya fueron guardados en la base de datos.');
            $this->warn('   Se recomienda hacer una revisión manual de los contenidos.');
        } else {
            $this->newLine();
            $this->comment('💡 Para reemplazar automáticamente los enlaces, ejecuta:');
            $this->comment('   php artisan domain:find-dev-links --replace');
        }
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return Command::SUCCESS;
    }
}
