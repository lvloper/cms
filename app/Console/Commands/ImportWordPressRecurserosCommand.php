<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Recursero;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Enums\EstablishmentType;
use App\Enums\ProfessionalType;
use App\Enums\Province;
use App\Enums\Theme;
use App\Enums\Status;

class ImportWordPressRecurserosCommand extends Command
{
    protected $signature = 'import:wordpress-recurseros {--test} {--clean} {--force}';
    protected $description = 'Import recurseros (resource guides) from WordPress database';

    public function handle()
    {
        $this->info('Starting WordPress Recurseros Import...');
        
        // Test WordPress connection
        if (!$this->testWordPressConnection()) {
            $this->error('Cannot connect to WordPress database. Please check your configuration.');
            return 1;
        }
        
        $isTest = $this->option('test');
        $shouldClean = $this->option('clean');
        $shouldForce = $this->option('force');
        
        if ($isTest) {
            $this->info('Running in TEST mode - no data will be saved');
        }
        
        if ($shouldClean && !$isTest) {
            $this->cleanExistingRecurseros();
        }
        
        $recurseros = $this->getWordPressRecurseros();
        
        if (empty($recurseros)) {
            $this->warn('No recurseros found in WordPress database');
            return 0;
        }
        
        $this->info("Found " . count($recurseros) . " recurseros to process");
        
        $stats = [
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0
        ];
        
        foreach ($recurseros as $recurseroData) {
            $stats['processed']++;
            
            try {
                $processedData = $this->processRecurseroData($recurseroData);
                
                if ($isTest) {
                    $this->displayRecurseroInfo($processedData);
                    continue;
                }
                
                $result = $this->processRecursero($processedData, $shouldForce);
                $stats[$result]++;
                
            } catch (\Exception $e) {
                $stats['errors']++;
                $title = is_object($recurseroData) ? ($recurseroData->establecimiento ?? 'Unknown') : ($recurseroData['title'] ?? 'Unknown');
                $this->error("Error processing recursero '{$title}': " . $e->getMessage());
            }
        }
        
        $this->displayStats($stats, $isTest);
        
        return 0;
    }

    private function testWordPressConnection(): bool
    {
        try {
            DB::connection('wordpress')->select('SELECT 1');
            $this->info('✓ WordPress database connection successful');
            return true;
        } catch (\Exception $e) {
            $this->error('✗ WordPress database connection failed: ' . $e->getMessage());
            return false;
        }
    }

    private function cleanExistingRecurseros(): void
    {
        $this->info('Cleaning existing recurseros...');
        
        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Truncate recurseros table
            DB::table('recurseros')->truncate();
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            $this->info('✓ Existing recurseros cleaned successfully');
            
        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            throw new \Exception('Failed to clean existing recurseros: ' . $e->getMessage());
        }
    }

    private function getWordPressRecurseros(): array
    {
        $this->info('Fetching recurseros from WordPress database...');
        
        // Get all published rectrans_servicios posts
        $query = "
            SELECT ID 
            FROM wp_posts 
            WHERE post_type = 'rectrans_servicios' 
            AND post_status = 'publish'
            ORDER BY ID ASC
        ";
        
        $postIds = DB::connection('wordpress')->select($query);
        $recurseros = [];
        
        foreach ($postIds as $post) {
            $recursero = $this->getRecurseroData($post->ID);
            if ($recursero) {
                $recurseros[] = $recursero;
            }
        }
        
        return $recurseros;
    }

    private function getRecurseroData($postId)
    {
        try {
            // Get service data
            $servicio = $this->getMetaValue($postId, 'servicio_servicios');
            $area = $this->getMetaValue($postId, 'servicio_area');
            $horarios = $this->getMetaValue($postId, 'servicio_horarios');
            $telefono = $this->getMetaValue($postId, 'servicio_telefono');
            $email = $this->getMetaValue($postId, 'servicio_email');
            $web = $this->getMetaValue($postId, 'servicio_web');
            $comentarios = $this->getMetaValue($postId, 'servicio_comentarios');
            
            // Get establishment ID
            $establecimientoRaw = $this->getMetaValue($postId, 'servicio_establecimiento');
            $establecimientoData = @unserialize($establecimientoRaw);
            $establecimientoId = is_array($establecimientoData) ? $establecimientoData[0] : null;
            
            if (!$establecimientoId) {
                $this->warn("No establishment found for post ID: {$postId}");
                return null;
            }
            
            // Get establishment data
            $establecimiento = $this->getMetaValue($establecimientoId, 'establecimiento_nombre');
            $tipo = $this->getMetaValue($establecimientoId, 'establecimiento_tipo');
            $calle = $this->getMetaValue($establecimientoId, 'establecimiento_calle');
            $numero = $this->getMetaValue($establecimientoId, 'establecimiento_numero');
            $depto = $this->getMetaValue($establecimientoId, 'establecimiento_depto');
            $provincia = $this->getMetaValue($establecimientoId, 'establecimiento_provincia');
            // Some sites store city/locality under different keys, try a few
            $localidad = $this->getMetaValue($establecimientoId, 'establecimiento_localidad')
                ?: $this->getMetaValue($establecimientoId, 'establecimiento_ciudad')
                ?: $this->getMetaValue($establecimientoId, 'establecimiento_municipio')
                ?: null;
            
            // Get professionals
            $profesionalRaw = $this->getMetaValue($postId, 'servicio_profesional');
            $profesionalData = @unserialize($profesionalRaw);
            $profesionales = [];
            
            if (is_array($profesionalData)) {
                foreach ($profesionalData as $profId) {
                    $profTitle = DB::connection('wordpress')
                        ->select("SELECT post_title FROM wp_posts WHERE ID = ? LIMIT 1", [$profId])[0]->post_title ?? null;
                    if ($profTitle) {
                        $profesionales[] = $profTitle;
                    }
                }
            }
            
            return (object) [
                'id' => $postId,
                'servicio' => $servicio,
                'area' => $area,
                'horarios' => $horarios,
                'telefono' => $telefono,
                'email' => $email,
                'web' => $web,
                'comentarios' => $comentarios,
                'establecimiento' => $establecimiento,
                'tipo' => $tipo,
                'calle' => $calle,
                'numero' => $numero,
                'depto' => $depto,
                'provincia' => $provincia,
                'localidad' => $localidad,
                'profesionales' => implode(', ', $profesionales)
            ];
            
    } catch (\Exception $e) {
            $this->error("Error processing post ID {$postId}: " . $e->getMessage());
            return null;
        }
    }
    
    private function getMetaValue($postId, $metaKey)
    {
        return DB::connection('wordpress')
            ->select("SELECT meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key = ? LIMIT 1", [$postId, $metaKey])[0]->meta_value ?? null;
    }
    
    private function processRecurseroData($recursero)
    {
        $title = $recursero->establecimiento ?: $recursero->servicio ?: 'Sin título';
        $baseSlug = Str::slug($title) ?: 'sin-titulo';
        $slug = $baseSlug . '-' . $recursero->id;
        
        return [
            'wp_id' => $recursero->id,
            'title' => $title,
            'slug' => $slug,
            'content' => $recursero->comentarios,
            // Try to preserve legacy values if they are already valid enum values; fallback to mapping
            'establishment_type' => $this->coerceEnumValue($recursero->tipo, \App\Enums\EstablishmentType::class)
                ?? $this->mapEstablishmentType($recursero->tipo),
            'professional_type' => $this->coerceEnumValue($recursero->profesionales, \App\Enums\ProfessionalType::class)
                ?? $this->mapProfessionalType($recursero->profesionales),
            'province' => $this->coerceEnumValue($recursero->provincia, \App\Enums\Province::class)
                ?? $this->mapProvince($recursero->provincia),
            'theme' => $this->coerceEnumValue($recursero->servicio, \App\Enums\Theme::class)
                ?? $this->mapTheme($recursero->servicio),
            'address' => trim(($recursero->calle ?: '') . ' ' . ($recursero->numero ?: '') . ' ' . ($recursero->depto ?: '')),
            'locality' => $recursero->localidad ?? null,
            'phone' => $recursero->telefono,
            'email' => $recursero->email,
            'website' => $recursero->web,
            'schedule' => $recursero->horarios,
            'comments' => $recursero->comentarios,
            'status' => Status::Published,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function getImageUrl($thumbnailId): ?string
    {
        try {
            // Get attachment post and metadata
            $attachment = DB::connection('wordpress')->select(
                "SELECT p.guid, p.post_title, pm.meta_value as attached_file 
                 FROM wp_posts p 
                 LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file'
                 WHERE p.ID = ? AND p.post_type = 'attachment'",
                [$thumbnailId]
            );
            
            if (!empty($attachment)) {
                $attachmentData = $attachment[0];
                
                // Try to get the full image URL from guid first
                if ($attachmentData->guid) {
                    // Ensure the URL is complete and accessible
                    $imageUrl = $attachmentData->guid;
                    
                    // If guid doesn't contain full domain, construct it
                    if (!str_contains($imageUrl, 'http')) {
                        $imageUrl = 'https://huesped.org.ar' . $imageUrl;
                    }
                    
                    return $imageUrl;
                }
                
                // Fallback: construct URL from attached_file metadata
                if ($attachmentData->attached_file) {
                    return 'https://huesped.org.ar/wp-content/uploads/' . $attachmentData->attached_file;
                }
            }
        } catch (\Exception $e) {
            $this->warn("Error getting image for thumbnail ID {$thumbnailId}: " . $e->getMessage());
        }
        
        return null;
    }

    private function processRecursero(array $recurseroData, bool $shouldForce): string
    {
        // Check if recursero already exists
        $existingRecursero = Recursero::where('wp_id', $recurseroData['wp_id'])->first();
        
        if ($existingRecursero) {
            if (!$shouldForce) {
                $this->line("Skipping existing recursero: {$recurseroData['title']}");
                return 'skipped';
            }
            
            // Update existing recursero
            $existingRecursero->update([
                'title' => $recurseroData['title'],
                'slug' => $recurseroData['slug'],
                'content' => $recurseroData['content'],
                'establishment_type' => $recurseroData['establishment_type'],
                'professional_type' => $recurseroData['professional_type'],
                'province' => $recurseroData['province'],
                'theme' => $recurseroData['theme'],
                'address' => $recurseroData['address'],
                'locality' => $recurseroData['locality'] ?? null,
                'phone' => $recurseroData['phone'],
                'email' => $recurseroData['email'],
                'website' => $recurseroData['website'],
                'schedule' => $recurseroData['schedule'],
                'comments' => $recurseroData['comments'],
                'published_at' => $recurseroData['published_at']
            ]);
            
            // Update route status
            if ($existingRecursero->route) {
                $existingRecursero->route->update(['status' => $recurseroData['status']]);
            }
            
            $this->line("Updated recursero: {$recurseroData['title']}");
            return 'updated';
        }
        
        // Create new recursero
        $recursero = Recursero::create([
            'wp_id' => $recurseroData['wp_id'],
            'title' => $recurseroData['title'],
            'slug' => $recurseroData['slug'],
            'content' => $recurseroData['content'],
            'establishment_type' => $recurseroData['establishment_type'],
            'professional_type' => $recurseroData['professional_type'],
            'province' => $recurseroData['province'],
            'theme' => $recurseroData['theme'],
            'address' => $recurseroData['address'],
            'locality' => $recurseroData['locality'] ?? null,
            'phone' => $recurseroData['phone'],
            'email' => $recurseroData['email'],
            'website' => $recurseroData['website'],
            'schedule' => $recurseroData['schedule'],
            'comments' => $recurseroData['comments'],
            'published_at' => $recurseroData['published_at']
        ]);
        
        // Set route status
        if ($recursero->route) {
            $recursero->route->update(['status' => $recurseroData['status']]);
        }
        
        $this->line("Created recursero: {$recurseroData['title']}");
        return 'created';
    }

    private function displayRecurseroInfo(array $recurseroData): void
    {
        $this->info("Recursero: {$recurseroData['title']}");
        $this->line("  WP ID: {$recurseroData['wp_id']}");
        $this->line("  Content: " . Str::limit($recurseroData['content'] ?: '', 100));
        $this->line("  Establishment Type: " . ($recurseroData['establishment_type'] ?: 'None'));
        $this->line("  Professional Type: " . ($recurseroData['professional_type'] ?: 'None'));
        $this->line("  Province: " . ($recurseroData['province'] ?: 'None'));
        $this->line("  Theme: " . ($recurseroData['theme'] ?: 'None'));
        $this->line("  Address: " . ($recurseroData['address'] ?: 'None'));
        $this->line("  Phone: " . ($recurseroData['phone'] ?: 'None'));
        $this->line("  Email: " . ($recurseroData['email'] ?: 'None'));
        $this->line("  Website: " . ($recurseroData['website'] ?: 'None'));
        $this->line("  Schedule: " . ($recurseroData['schedule'] ?: 'None'));
        $this->line("  Comments: " . Str::limit($recurseroData['comments'] ?: '', 50));
        $this->line("  Published: {$recurseroData['published_at']}");
        $this->line('');
    }

    private function mapEstablishmentType(?string $wpType): ?EstablishmentType
    {
        if (!$wpType) return null;
        
        $wpType = strtolower(trim($wpType));
        
        return match($wpType) {
            'público', 'publico', 'estatal', 'municipal', 'provincial', 'nacional' => EstablishmentType::PUBLIC,
            'privado', 'particular' => EstablishmentType::PRIVATE,
            'ong', 'organización no gubernamental', 'organizacion no gubernamental' => EstablishmentType::NGO,
            'cooperativa' => EstablishmentType::COOPERATIVE,
            'fundación', 'fundacion' => EstablishmentType::FOUNDATION,
            'asociación', 'asociacion' => EstablishmentType::ASSOCIATION,
            'clínica', 'clinica' => EstablishmentType::CLINIC,
            'hospital' => EstablishmentType::HOSPITAL,
            'centro' => EstablishmentType::CENTER,
            'consultorio' => EstablishmentType::OFFICE,
            default => null,
        };
    }
    
    private function mapProfessionalType(?string $wpProfessional): ?ProfessionalType
    {
        if (!$wpProfessional) return null;
        
        $wpProfessional = strtolower(trim($wpProfessional));
        
        return match(true) {
            str_contains($wpProfessional, 'psicólog') || str_contains($wpProfessional, 'psicolog') => ProfessionalType::PSYCHOLOGIST,
            str_contains($wpProfessional, 'psiquiatra') => ProfessionalType::PSYCHIATRIST,
            str_contains($wpProfessional, 'médico') || str_contains($wpProfessional, 'medico') => ProfessionalType::DOCTOR,
            str_contains($wpProfessional, 'trabajador social') || str_contains($wpProfessional, 'asistente social') => ProfessionalType::SOCIAL_WORKER,
            str_contains($wpProfessional, 'abogad') => ProfessionalType::LAWYER,
            str_contains($wpProfessional, 'terapeuta') => ProfessionalType::THERAPIST,
            str_contains($wpProfessional, 'consejero') || str_contains($wpProfessional, 'consejera') => ProfessionalType::COUNSELOR,
            str_contains($wpProfessional, 'sexólog') || str_contains($wpProfessional, 'sexolog') => ProfessionalType::SEXOLOGIST,
            str_contains($wpProfessional, 'endocrinólog') || str_contains($wpProfessional, 'endocrinolog') => ProfessionalType::ENDOCRINOLOGIST,
            str_contains($wpProfessional, 'ginecólog') || str_contains($wpProfessional, 'ginecolog') => ProfessionalType::GYNECOLOGIST,
            str_contains($wpProfessional, 'urólog') || str_contains($wpProfessional, 'urolog') => ProfessionalType::UROLOGIST,
            str_contains($wpProfessional, 'cirujano plástico') || str_contains($wpProfessional, 'cirujana plástica') => ProfessionalType::PLASTIC_SURGEON,
            default => null,
        };
    }
    
    private function mapProvince(?string $wpProvince): ?Province
    {
        if (!$wpProvince) return null;
        
        $wpProvince = strtolower(trim($wpProvince));
        
        return match($wpProvince) {
            'buenos aires', 'pcia. de buenos aires', 'provincia de buenos aires' => Province::BUENOS_AIRES,
            'catamarca' => Province::CATAMARCA,
            'chaco' => Province::CHACO,
            'chubut' => Province::CHUBUT,
            'córdoba', 'cordoba' => Province::CORDOBA,
            'corrientes' => Province::CORRIENTES,
            'entre ríos', 'entre rios' => Province::ENTRE_RIOS,
            'formosa' => Province::FORMOSA,
            'jujuy' => Province::JUJUY,
            'la pampa' => Province::LA_PAMPA,
            'la rioja' => Province::LA_RIOJA,
            'mendoza' => Province::MENDOZA,
            'misiones' => Province::MISIONES,
            'neuquén', 'neuquen' => Province::NEUQUEN,
            'río negro', 'rio negro' => Province::RIO_NEGRO,
            'salta' => Province::SALTA,
            'san juan' => Province::SAN_JUAN,
            'san luis' => Province::SAN_LUIS,
            'santa cruz' => Province::SANTA_CRUZ,
            'santa fe' => Province::SANTA_FE,
            'santiago del estero' => Province::SANTIAGO_DEL_ESTERO,
            'tierra del fuego' => Province::TIERRA_DEL_FUEGO,
            'tucumán', 'tucuman' => Province::TUCUMAN,
            'caba', 'ciudad autónoma de buenos aires', 'capital federal' => Province::CABA,
            default => null,
        };
    }
    
    private function mapTheme(?string $wpTheme): ?Theme
    {
        if (!$wpTheme) return null;
        
        $wpTheme = strtolower(trim($wpTheme));
        
        return match(true) {
            str_contains($wpTheme, 'identidad de género') || str_contains($wpTheme, 'identidad de genero') => Theme::GENDER_IDENTITY,
            str_contains($wpTheme, 'orientación sexual') || str_contains($wpTheme, 'orientacion sexual') => Theme::SEXUAL_ORIENTATION,
            str_contains($wpTheme, 'derechos humanos') => Theme::HUMAN_RIGHTS,
            str_contains($wpTheme, 'violencia') => Theme::VIOLENCE,
            str_contains($wpTheme, 'discriminación') || str_contains($wpTheme, 'discriminacion') => Theme::DISCRIMINATION,
            str_contains($wpTheme, 'legal') || str_contains($wpTheme, 'jurídic') || str_contains($wpTheme, 'juridic') => Theme::LEGAL,
            str_contains($wpTheme, 'psicológic') || str_contains($wpTheme, 'psicologic') => Theme::PSYCHOLOGICAL,
            str_contains($wpTheme, 'médic') || str_contains($wpTheme, 'medic') => Theme::MEDICAL,
            str_contains($wpTheme, 'social') => Theme::SOCIAL,
            str_contains($wpTheme, 'salud sexual') => Theme::SEXUAL_HEALTH,
            str_contains($wpTheme, 'terapia hormonal') || str_contains($wpTheme, 'hormon') => Theme::HORMONE_THERAPY,
            str_contains($wpTheme, 'cirugía') || str_contains($wpTheme, 'cirugia') => Theme::SURGERY,
            str_contains($wpTheme, 'apoyo familiar') || str_contains($wpTheme, 'familia') => Theme::FAMILY_SUPPORT,
            str_contains($wpTheme, 'educación') || str_contains($wpTheme, 'educacion') => Theme::EDUCATION,
            str_contains($wpTheme, 'empleo') || str_contains($wpTheme, 'trabajo') => Theme::EMPLOYMENT,
            default => null,
        };
    }

    private function displayStats(array $stats, bool $isTest): void
    {
        $this->info('');
        $this->info('=== Import Summary ===');
        
        if ($isTest) {
            $this->info("Processed: {$stats['processed']} recurseros (TEST MODE)");
        } else {
            $this->info("Processed: {$stats['processed']} recurseros");
            $this->info("Created: {$stats['created']}");
            $this->info("Updated: {$stats['updated']}");
            $this->info("Skipped: {$stats['skipped']}");
            $this->info("Errors: {$stats['errors']}");
        }
    }

    /**
     * If $raw equals one of the enum values, return it as-is. Also try a slug normalization.
     */
    private function coerceEnumValue(?string $raw, string $enumClass): ?string
    {
        if (!$raw) return null;

        $raw = trim($raw);
        $values = array_map(fn($c) => $c->value, $enumClass::cases());

        // Exact match
        if (in_array($raw, $values, true)) {
            return $raw;
        }

        // Slug/normalize and try again
        $normalized = Str::slug($raw, '_');
        if (in_array($normalized, $values, true)) {
            return $normalized;
        }

        return null;
    }
}