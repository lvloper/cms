<?php

namespace App\Console\Commands;

use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;


class MagicFilamentBlock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    
    protected $signature = 'magic:filament-block:form {class} {--prompt}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate with magic a form for your block';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $className = $this->argument('class');
        $generatePrompt = $this->option('prompt');

        $namespace = $this->getDefaultNamespace('App');
        $blockClassPath = base_path('app/Filament/Blocks/' . $className . 'Block.php');

        $this->info('Thinking...');
        
        $bladePath = resource_path('views/blocks/' . $className . '.blade.php');
        $bladeContent = file_get_contents($bladePath);

        $prompt = $this->generatePrompt($bladeContent);

        if ($generatePrompt) {
            $this->info('Prompt generated');
            $this->question($prompt);
            return;
        } 

        $magic = $this->generateForm($prompt);
        if (!$magic) return;

        // extract the schema from the magic
        $magic = $this->extractSchema($magic);

        $this->info('Showing the magic...');
        $this->question($magic);
        $this->info('Does it look good?');

        if ($this->confirm("Do you want to overwrite {$blockClassPath}?")) {
            $this->replaceSchema($blockClassPath, $magic);
        }
    }

    protected function extractSchema($input) {
        // Expresión regular para encontrar el bloque $schema = [ ... ];
        $pattern = '/\$schema\s*=\s*\[\s*(.*?)\s*\];/s';
    
        // Buscar el bloque en el contenido de entrada
        if (preg_match($pattern, $input, $matches)) {
            // Devolver solo el contenido del bloque $schema
            return <<<CODE
        \$schema = [
            $matches[1]
        ];
        CODE;
        }
    
        // Si no se encuentra el bloque, devolver una cadena vacía o un mensaje de error
        return 'No se encontró el bloque $schema.';
    }
    
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Filament\Blocks';
    }

    protected function replaceSchema($file, $newSchema = '$Schema = [];')
    {
        $filesystem = new Filesystem();
        $content = $filesystem->get($file);

        $newContent = preg_replace('/\$schema\s*=\s*\[.*?\];/s', $newSchema, $content);


        $filesystem->put($file, $newContent);
    }

    protected function generatePrompt($blockView)
    {
        $filamentFieldsDocumentation = file_get_contents(base_path('iaData/filamentFieldsDocumentation.txt'));
        $filamentFormExamples = file_get_contents(base_path('iaData/filamentFormExamples.txt'));

        $prompt = "
            Context:
            You can use the following components:

            {$filamentFieldsDocumentation}

            Some examples;

            {$filamentFormExamples}

            Important: Always follow these instructions:
            - The output will be the \$schema variable with the fields for the block in Array.
            - Dont add comments in the schema, use the comments in the prompt.
            - Output always starts at \'\$schema' and ends at '];'

            -------------
            Your task is:

            Provide the fields for the block.

            Use this input:

            {$blockView}

            Output:
        ";

        return $prompt;
    }

    protected function generateForm($prompt)
    {
        $response = Ollama::model(config('ollama-laravel.filament-magic-model'))
                ->prompt($prompt)
                ->options(['temperature' => 0.8])
                ->stream(false)
                ->ask();

        if( isset($response['error']) ) {
            
            $this->error($response['error']);
            $this->info("Ups! Ollama could not generate the form for you.");
            $this->info("Dont worry, you can copy the : \n-------------------\n PROMPT:");
            $this->question($prompt);
            $this->error("Ollama may be misconfigured, see above");
            return false;
        } else {
            return $response['response'];
        }
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