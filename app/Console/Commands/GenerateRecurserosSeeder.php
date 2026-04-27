<?php

namespace App\Console\Commands;

use App\Models\Recursero;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateRecurserosSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurseros:generate-seeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate RecurserosSeeder from current database data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating RecurserosSeeder from current database...');
        
        $recurseros = Recursero::all();
        
        if ($recurseros->isEmpty()) {
            $this->error('No recurseros found in database.');
            return 1;
        }
        
        $this->info("Found {$recurseros->count()} recurseros to export.");
        
        $seederContent = $this->generateSeederContent($recurseros);
        
        $seederPath = database_path('seeders/RecurserosSeeder.php');
        File::put($seederPath, $seederContent);
        
        $this->info("Seeder generated successfully at: {$seederPath}");
        $this->info("You can now run: php artisan db:seed --class=RecurserosSeeder");
        
        return 0;
    }
    
    private function generateSeederContent($recurseros)
    {
        $data = [];
        
        foreach ($recurseros as $recursero) {
            $data[] = [
                // Nombres de columnas del ODS - columnas B-R
                'establecimiento' => $recursero->title,                    // B - Establecimiento
                'tipo' => $recursero->establishment_type,                  // C - Tipo
                'calle' => $recursero->address,                           // D - Calle
                'numero' => $recursero->street_number,                    // E - Número
                'cruce' => $recursero->cross_street,                      // F - Cruce
                'depto' => $recursero->department,                        // G - Depto
                'partido' => $recursero->district,                        // H - Partido
                'provincia' => $recursero->province?->value ?? $recursero->province, // I - Provincia
                'gmaps' => $recursero->google_maps_url,                   // J - Google Maps
                'profesional' => $recursero->professional_name,           // K - Profesional
                'servicio' => $recursero->service_type,                   // L - Servicio
                'area' => $recursero->service_area,                       // M - Área
                'horarios' => $recursero->schedule,                       // N - Horarios
                'telefono' => $recursero->phone,                          // O - Teléfono
                'email' => $recursero->email,                             // P - Email
                'web' => $recursero->website,                             // Q - Web
                'comentarios' => $recursero->comments,                    // R - Comentarios
                
                // Campos técnicos de Laravel (no del ODS)
                'slug' => $recursero->slug,
                'locality' => $recursero->locality,
                'professional_type' => $recursero->professional_type,
                'status' => $recursero->status?->value ?? $recursero->status,
                'published_at' => $recursero->published_at?->format('Y-m-d H:i:s'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ];
        }
        
        $dataString = var_export($data, true);
        
        return <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Recursero;

class RecurserosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Data structure matches ODS columns:
     * B=establecimiento, C=tipo, D=calle, E=numero, F=cruce, G=depto, H=partido, I=provincia
     * J=gmaps, K=profesional, L=servicio, M=area, N=horarios, O=telefono, P=email, Q=web, R=comentarios
     */
    public function run(): void
    {
        // Truncar tabla antes de insertar
        Recursero::truncate();
        
        \$odsData = {$dataString};
        
        // Mapear datos del ODS a campos de la base de datos
        \$dbData = [];
        foreach (\$odsData as \$row) {
            \$dbData[] = [
                'title' => \$row['establecimiento'],
                'slug' => \$row['slug'],
                'establishment_type' => \$row['tipo'],
                'professional_type' => \$row['professional_type'] ?? '',
                'province' => \$row['provincia'],
                'address' => \$row['calle'],
                'street_number' => \$row['numero'],
                'cross_street' => \$row['cruce'],
                'department' => \$row['depto'],
                'locality' => \$row['locality'] ?? '',
                'district' => \$row['partido'],
                'google_maps_url' => \$row['gmaps'],
                'professional_name' => \$row['profesional'],
                'service_type' => \$row['servicio'],
                'service_area' => \$row['area'],
                'schedule' => \$row['horarios'],
                'phone' => \$row['telefono'],
                'email' => \$row['email'],
                'website' => \$row['web'],
                'comments' => \$row['comentarios'],
                'status' => \$row['status'],
                'published_at' => \$row['published_at'],
                'created_at' => \$row['created_at'],
                'updated_at' => \$row['updated_at'],
            ];
        }
        
        // Insertar en lotes para mejor rendimiento
        \$chunks = array_chunk(\$dbData, 50);
        
        foreach (\$chunks as \$chunk) {
            DB::table('recurseros')->insert(\$chunk);
        }
        
        \$this->command->info('Recurseros seeded successfully: ' . count(\$dbData) . ' records');
        \$this->command->info('Data structure matches ODS columns B-R (establecimiento to comentarios)');
    }
}
PHP;
    }
}
