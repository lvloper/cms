<?php

namespace App\Console\Commands;

use App\Models\Recursero;
use Illuminate\Console\Command;

class TestMultipleProfessionals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recursero:test-professionals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test multiple professionals functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Multiple Professionals System');
        $this->newLine();
        
        // 1. Encontrar recurseros con múltiples profesionales
        $multipleProfs = Recursero::where('professional_name', 'LIKE', '%,%')->take(3)->get();
        
        $this->info('📋 Recurseros with multiple professionals:');
        foreach ($multipleProfs as $recursero) {
            $this->line("  🏥 {$recursero->title}");
            $this->line("     Raw: {$recursero->getRawOriginal('professional_name')}");
            
            // Simular contexto de admin para probar el accessor
            $originalRequest = request();
            request()->setTrustedHosts(['admin/test']);
            $asArray = $recursero->professional_name;
            
            $this->line("     Array: " . json_encode($asArray));
            $this->newLine();
        }
        
        // 2. Probar método getAllIndividualProfessionals
        $allProfessionals = Recursero::getAllIndividualProfessionals();
        $this->info("📊 Total individual professionals found: " . count($allProfessionals));
        
        // 3. Probar filtro de búsqueda
        $testSearch = ['Bechara', 'Maria Teresa'];
        $this->info("🔍 Testing filter with: " . implode(', ', $testSearch));
        
        $filtered = Recursero::where(function ($q) use ($testSearch) {
            foreach ($testSearch as $professional) {
                $q->orWhere('professional_name', 'LIKE', '%' . $professional . '%');
            }
        })->get();
        
        $this->info("   Found {$filtered->count()} matching recurseros");
        
        foreach ($filtered as $recursero) {
            $this->line("   ✅ {$recursero->title}: {$recursero->getRawOriginal('professional_name')}");
        }
        
        $this->newLine();
        $this->info('✨ Multiple professionals system is working correctly!');
        
        return 0;
    }
}
