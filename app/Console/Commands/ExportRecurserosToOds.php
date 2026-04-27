<?php

namespace App\Console\Commands;

use App\Models\Recursero;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportRecurserosToOds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurseros:export-csv {file?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export recurseros to CSV with ODS column names';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('file') ?? 'recurseros_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $this->info('Exporting recurseros to CSV with ODS column structure...');
        
        $recurseros = Recursero::all();
        
        if ($recurseros->isEmpty()) {
            $this->error('No recurseros found to export.');
            return 1;
        }
        
        $this->info("Found {$recurseros->count()} recurseros to export.");
        
        // Headers que coinciden con las columnas del ODS (B-R)
        $headers = [
            'establecimiento',   // B
            'tipo',             // C
            'calle',            // D
            'numero',           // E
            'cruce',            // F
            'depto',            // G
            'partido',          // H
            'provincia',        // I
            'gmaps',            // J
            'profesional',      // K
            'servicio',         // L
            'area',             // M
            'horarios',         // N
            'telefono',         // O
            'email',            // P
            'web',              // Q
            'comentarios'       // R
        ];
        
        $csvContent = $this->generateCsvContent($recurseros, $headers);
        
        File::put($filename, $csvContent);
        
        $this->info("Export completed successfully: {$filename}");
        $this->info("Columns match ODS structure (B-R): establecimiento to comentarios");
        
        return 0;
    }
    
    private function generateCsvContent($recurseros, $headers)
    {
        $lines = [];
        
        // Agregar headers
        $lines[] = $this->arrayToCsv($headers);
        
        // Agregar datos
        foreach ($recurseros as $recursero) {
            $row = [
                $recursero->title,                                    // establecimiento (B)
                $recursero->establishment_type,                       // tipo (C)
                $recursero->address,                                  // calle (D)
                $recursero->street_number,                           // numero (E)
                $recursero->cross_street,                            // cruce (F)
                $recursero->department,                              // depto (G)
                $recursero->district,                                // partido (H)
                $recursero->province?->value ?? $recursero->province, // provincia (I)
                $recursero->google_maps_url,                         // gmaps (J)
                $recursero->professional_name,                       // profesional (K)
                $recursero->service_type,                            // servicio (L)
                $recursero->service_area,                            // area (M)
                $recursero->schedule,                                // horarios (N)
                $recursero->phone,                                   // telefono (O)
                $recursero->email,                                   // email (P)
                $recursero->website,                                 // web (Q)
                $recursero->comments,                                // comentarios (R)
            ];
            
            $lines[] = $this->arrayToCsv($row);
        }
        
        return implode("\n", $lines);
    }
    
    private function arrayToCsv($fields)
    {
        $escaped = array_map(function($field) {
            // Escapar comillas dobles y envolver en comillas si contiene comas o saltos de línea
            $field = str_replace('"', '""', $field ?? '');
            if (strpos($field, ',') !== false || strpos($field, "\n") !== false || strpos($field, '"') !== false) {
                $field = '"' . $field . '"';
            }
            return $field;
        }, $fields);
        
        return implode(',', $escaped);
    }
}
