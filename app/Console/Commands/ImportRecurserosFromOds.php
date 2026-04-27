<?php

namespace App\Console\Commands;

use App\Models\Recursero;
use App\Enums\Status;
use App\Enums\Province;
use App\Enums\ServicioType;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class ImportRecurserosFromOds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurseros:import-ods {file?} {--truncate : Truncate existing records before import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import recurseros data from Excel file (.xlsx or .ods)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
    $filePath = $this->argument('file') ?? 'database/data/2025OCT28 TransTI _ Recursero Fundación Huésped - Limpieza de base.csv';

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        // Truncar tabla si se especifica la opción
        if ($this->option('truncate')) {
            $this->info("Truncating existing recurseros...");
            Recursero::truncate();
            $this->info("Existing records deleted.");
        }

        $this->info("Starting import from: {$filePath}");

        // Detectar tipo de archivo y leer
        try {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if ($extension === 'xlsx') {
                $reader = IOFactory::createReader('Xlsx');
            } elseif ($extension === 'ods') {
                $reader = IOFactory::createReader('Ods');
            } elseif ($extension === 'csv') {
                $reader = new Csv();
                $reader->setDelimiter(',');
                $reader->setEnclosure('"');
                $reader->setSheetIndex(0);
                $reader->setInputEncoding('UTF-8');
            } else {
                $this->error("Unsupported file extension: .{$extension}");
                return 1;
            }

            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $this->parseSpreadsheetData($worksheet);
        } catch (\Exception $e) {
            $this->error("Failed to read file: " . $e->getMessage());
            return 1;
        }

        $this->info("Found " . count($data) . " records to import");

        // Importar datos
        $imported = 0;
        $errors = 0;

        $this->withProgressBar($data, function ($row) use (&$imported, &$errors) {
            try {
                $this->importRecord($row);
                $imported++;
            } catch (\Exception $e) {
                $this->error("Error importing record: " . $e->getMessage());
                $errors++;
            }
        });

        $this->newLine(2);
        $this->info("Import completed:");
        $this->info("- Imported: {$imported}");
        $this->info("- Errors: {$errors}");

        return 0;
    }

    private function parseSpreadsheetData($worksheet)
    {
        $rows = $worksheet->toArray(null, true, true, true);

        if (empty($rows) || count($rows) === 1) {
            return [];
        }

        $headerRow = array_shift($rows);
        $columnMap = [];
        foreach ($headerRow as $column => $value) {
            $normalized = $this->normalizeHeader($value);
            if ($normalized !== null) {
                $columnMap[$column] = $normalized;
            }
        }

        if (!in_array('establecimiento', $columnMap, true)) {
            return $this->parseLegacySpreadsheetData($worksheet);
        }

        $data = [];
        foreach ($rows as $row) {
            $record = [];
            foreach ($columnMap as $column => $field) {
                $record[$field] = $this->cleanText($row[$column] ?? '');
            }

            $establecimiento = $record['establecimiento'] ?? '';
            if (empty($establecimiento) || str_contains($establecimiento, '#¿NOMBRE?')) {
                continue;
            }

            $data[] = [
                'establecimiento' => $establecimiento,
                'tipo' => $record['tipo'] ?? '',
                'calle' => $record['calle'] ?? '',
                'numero' => $record['numero'] ?? '',
                'cruce' => $record['cruce'] ?? '',
                'depto' => $record['depto'] ?? '',
                'partido' => $record['partido'] ?? '',
                'provincia' => $record['provincia'] ?? '',
                'gmaps' => $record['gmaps'] ?? '',
                'profesional' => $record['profesional'] ?? '',
                'servicio' => $record['servicio'] ?? '',
                'area' => $record['area'] ?? '',
                'horarios' => $record['horarios'] ?? '',
                'telefono' => $record['telefono'] ?? '',
                'email' => $record['email'] ?? '',
                'web' => $record['web'] ?? '',
                'comentarios' => $record['comentarios'] ?? '',
            ];
        }

        return $data;
    }

    private function parseLegacySpreadsheetData($worksheet): array
    {
        $data = [];
        $highestRow = $worksheet->getHighestRow();

        for ($row = 2; $row <= $highestRow; $row++) {
            $establecimiento = $worksheet->getCell("B{$row}")->getCalculatedValue();

            if (empty($establecimiento) || strpos($establecimiento, '#¿NOMBRE?') !== false) {
                continue;
            }

            $data[] = [
                'establecimiento' => $this->cleanText($establecimiento),
                'tipo' => $this->cleanText($worksheet->getCell("C{$row}")->getCalculatedValue()),
                'calle' => $this->cleanText($worksheet->getCell("D{$row}")->getCalculatedValue()),
                'numero' => $this->cleanText($worksheet->getCell("E{$row}")->getCalculatedValue()),
                'cruce' => $this->cleanText($worksheet->getCell("F{$row}")->getCalculatedValue()),
                'depto' => $this->cleanText($worksheet->getCell("G{$row}")->getCalculatedValue()),
                'partido' => $this->cleanText($worksheet->getCell("H{$row}")->getCalculatedValue()),
                'provincia' => $this->cleanText($worksheet->getCell("I{$row}")->getCalculatedValue()),
                'gmaps' => $this->cleanText($worksheet->getCell("J{$row}")->getCalculatedValue()),
                'profesional' => $this->cleanText($worksheet->getCell("K{$row}")->getCalculatedValue()),
                'servicio' => $this->cleanText($worksheet->getCell("L{$row}")->getCalculatedValue()),
                'area' => $this->cleanText($worksheet->getCell("M{$row}")->getCalculatedValue()),
                'horarios' => $this->cleanText($worksheet->getCell("N{$row}")->getCalculatedValue()),
                'telefono' => $this->cleanText($worksheet->getCell("O{$row}")->getCalculatedValue()),
                'email' => $this->cleanText($worksheet->getCell("P{$row}")->getCalculatedValue()),
                'web' => $this->cleanText($worksheet->getCell("Q{$row}")->getCalculatedValue()),
                'comentarios' => $this->cleanText($worksheet->getCell("R{$row}")->getCalculatedValue()),
            ];
        }

        return array_filter($data, function ($row) {
            return !empty($row['establecimiento']);
        });
    }

    private function normalizeHeader(?string $value): ?string
    {
        $normalized = trim(mb_strtolower((string) $value));

        return match ($normalized) {
            '#' => 'indice',
            'establecimiento' => 'establecimiento',
            'tipo' => 'tipo',
            'calle' => 'calle',
            'numero' => 'numero',
            'cruce' => 'cruce',
            'depto', 'departamento', 'departamento/piso' => 'depto',
            'partido', 'distrito' => 'partido',
            'provincia' => 'provincia',
            'gmaps', 'google maps', 'google_maps_url' => 'gmaps',
            'profesional', 'profesionales' => 'profesional',
            'servicio', 'servicios' => 'servicio',
            'area', 'área', 'area de servicio', 'área de servicio' => 'area',
            'horarios', 'horario' => 'horarios',
            'telefono', 'teléfono' => 'telefono',
            'email', 'mail' => 'email',
            'web', 'sitio web' => 'web',
            'comentarios', 'comentario' => 'comentarios',
            default => null,
        };
    }

    private function cleanText($text)
    {
        // Limpiar texto de tags XML y caracteres especiales
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim($text);
        return $text;
    }

    private function importRecord($row)
    {
        // Mapear provincia a enum
        $province = $this->mapProvince($row['provincia']);

        // Mapear servicio usando el nuevo enum
        $service = ServicioType::fromExcelValue($row['servicio']);

        // Crear slug único
        $baseSlug = Str::slug($row['establecimiento']);
        $slug = $baseSlug;
        $counter = 1;

        while (Recursero::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Crear registro
        Recursero::create([
            'title' => $row['establecimiento'],
            'slug' => $slug,
            'establishment_type' => $row['tipo'],
            'professional_type' => '', // Ya no se usa enum
            'province' => $province,
            'address' => $row['calle'],
            'street_number' => $row['numero'],
            'cross_street' => $row['cruce'],
            'department' => $row['depto'],
            'locality' => '', // Campo a mantener por compatibilidad
            'district' => $row['partido'],
            'google_maps_url' => $row['gmaps'],
            'professional_name' => $row['profesional'],
            'service_type' => $service, // Usar valor mapeado del enum
            'service_area' => $row['area'],
            'schedule' => $row['horarios'],
            'phone' => $row['telefono'],
            'email' => $row['email'],
            'website' => $row['web'],
            'comments' => $row['comentarios'],
            'status' => Status::Published,
            'published_at' => now(),
        ]);
    }

    private function mapProvince($provinceText)
    {
        $mapping = [
            'Buenos Aires' => Province::BUENOS_AIRES,
            'CABA' => Province::CABA,
            'Catamarca' => Province::CATAMARCA,
            'Chaco' => Province::CHACO,
            'Chubut' => Province::CHUBUT,
            'Córdoba' => Province::CORDOBA,
            'Corrientes' => Province::CORRIENTES,
            'Entre Ríos' => Province::ENTRE_RIOS,
            'Formosa' => Province::FORMOSA,
            'Jujuy' => Province::JUJUY,
            'La Pampa' => Province::LA_PAMPA,
            'La Rioja' => Province::LA_RIOJA,
            'Mendoza' => Province::MENDOZA,
            'Misiones' => Province::MISIONES,
            'Neuquén' => Province::NEUQUEN,
            'Río Negro' => Province::RIO_NEGRO,
            'Salta' => Province::SALTA,
            'San Juan' => Province::SAN_JUAN,
            'San Luis' => Province::SAN_LUIS,
            'Santa Cruz' => Province::SANTA_CRUZ,
            'Santa Fe' => Province::SANTA_FE,
            'Santiago del Estero' => Province::SANTIAGO_DEL_ESTERO,
            'Tierra del Fuego' => Province::TIERRA_DEL_FUEGO,
            'Tucumán' => Province::TUCUMAN,
        ];

        // Si no encuentra el mapeo exacto, intentar case-insensitive
        if (!isset($mapping[$provinceText])) {
            foreach ($mapping as $key => $value) {
                if (strcasecmp($key, $provinceText) === 0) {
                    return $value;
                }
            }
        }

        return $mapping[$provinceText] ?? Province::CABA; // Default fallback
    }
}
