<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;


class MakeFilamentBlock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:filament-block {class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Filament block';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $className = $this->argument('class');
        $namespace = $this->getDefaultNamespace('App');
        $path = base_path('app/Filament/Blocks/' . $className . 'Block.php');
    
        $this->makeDirectory($path);
    
        $stub = $this->getStub();
        $content = file_get_contents($stub);
        $content = str_replace(['{{ namespace }}', '{{ class }}'], [$namespace, $className], $content);
    
        file_put_contents($path, $content);
    
        $pathTemplate = base_path('app/Filament/Templates/DefaultTemplate.php');
        // New Blade template file creation
        $bladePath = resource_path('views/blocks/' . $className . '.blade.php');
        $this->makeDirectory($bladePath);
        $bladeContent = "<x-block> 
    <div class=\"px-10 font-bold\">Hello Word!!!</div>
</x-block>";
        
        file_put_contents($bladePath, $bladeContent);
        
        
        $this->info("\033[32mThe block created successfully.\033[0m");
        $this->info("\033[34mClass: $path\033[0m");
        $this->info("\033[34mView: $bladePath\033[0m \n");
        
        $this->info("\033[33mCopy in the $pathTemplate in \$blocks this code: \033[0m\n" .  
            "\033[36m    \App\Filament\Blocks\\" . $className . "Block::make(),\033[0m\n");
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Filament\Blocks';
    }

    protected function getStub()
    {
        return base_path('stubs/filament-block.stub');
    }

    protected function makeDirectory($path)
    {
        $filesystem = new Filesystem();
        $directory = dirname($path);

        if (!$filesystem->isDirectory($directory)) {
            $filesystem->makeDirectory($directory, 0755, true, true);
        }
    }
}