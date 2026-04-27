<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Page;
use App\Models\Route;
use Illuminate\Support\Facades\Process;

class CheckBlockImages extends Command
{
    protected $signature = 'blocks:check-images {--capture : Attempt to capture screenshots automatically}';
    protected $description = 'Check which blocks don\'t have images in public/img/admin/blocks';

    public function handle()
    {
        $this->info('Checking blocks without images...');
        
        // Get all blocks from DefaultTemplate
        $defaultTemplateContent = File::get(app_path('Filament/Templates/DefaultTemplate.php'));
        preg_match_all('/\\\\App\\\\Filament\\\\Blocks\\\\(\w+)Block::make\(\)/', $defaultTemplateContent, $matches);
        
        $blocks = $matches[1] ?? [];
        $missingBlocks = [];
        $blockImagePath = public_path('img/admin/blocks');
        
        // Get existing images
        $existingImages = [];
        if (File::exists($blockImagePath)) {
            $files = File::files($blockImagePath);
            foreach ($files as $file) {
                $existingImages[] = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            }
        }
        
        $this->info('Found ' . count($blocks) . ' blocks in DefaultTemplate');
        $this->info('Found ' . count($existingImages) . ' images in blocks directory');
        $this->newLine();
        
        // Check each block
        foreach ($blocks as $block) {
            $found = false;
            
            // Check different naming conventions
            $possibleNames = [
                $block,
                Str::snake($block),
                Str::camel($block),
                Str::lower($block)
            ];
            
            foreach ($possibleNames as $name) {
                if (in_array($name, $existingImages)) {
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $missingBlocks[] = $block;
            }
        }
        
        if (empty($missingBlocks)) {
            $this->info('✓ All blocks have images!');
            return Command::SUCCESS;
        }
        
        $this->error('Found ' . count($missingBlocks) . ' blocks without images:');
        $this->newLine();
        
        // Find pages using these blocks
        foreach ($missingBlocks as $block) {
            $this->warn("Block: {$block}Block");
            $this->line("Expected image name: {$block}.jpg or {$block}.png");
            
            // Search for pages using this block
            $pagesUsingBlock = $this->findPagesUsingBlock($block);
            
            if (!empty($pagesUsingBlock)) {
                $this->info("  Found in pages:");
                foreach ($pagesUsingBlock as $pageInfo) {
                    $this->line("    - {$pageInfo['title']} ({$pageInfo['url']})");
                }
            } else {
                $this->line("  Not currently used in any pages");
            }
            
            $this->newLine();
        }
        
        // Check if we should attempt automatic capture
        if ($this->option('capture')) {
            $this->info('Attempting automatic screenshot capture...');
            $this->captureScreenshots($missingBlocks);
        } else {
            $this->info('Manual screenshot instructions:');
            $this->info('1. Visit the page URL listed above');
            $this->info('2. Take a screenshot of the block');
            $this->info('3. Save it as: public/img/admin/blocks/[BlockName].jpg');
            $this->info('4. Recommended size: 600x400px');
            $this->newLine();
            $this->info('Tip: Run with --capture option to attempt automatic screenshots');
        }
        
        return Command::SUCCESS;
    }
    
    private function findPagesUsingBlock($blockName)
    {
        $pages = [];
        
        // Search in Pages model
        $pageRecords = Page::all();
        
        foreach ($pageRecords as $page) {
            if ($page->blocks && is_string($page->blocks)) {
                $blocks = json_decode($page->blocks, true);
            } else {
                $blocks = $page->blocks;
            }
            
            if ($this->searchBlockInData($blocks, $blockName)) {
                $pages[] = [
                    'title' => $page->title ?? 'Untitled',
                    'url' => $page->url ?? $page->getFullPath()
                ];
            }
        }
        
        return $pages;
    }
    
    private function searchBlockInData($data, $blockName)
    {
        if (!is_array($data)) {
            return false;
        }
        
        foreach ($data as $item) {
            if (isset($item['type']) && $item['type'] === $blockName) {
                return true;
            }
            
            // Recursively search in nested structures
            foreach ($item as $value) {
                if (is_array($value) && $this->searchBlockInData($value, $blockName)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    private function captureScreenshots($missingBlocks)
    {
        // Check if puppeteer is available
        $puppeteerAvailable = $this->checkPuppeteer();
        
        if (!$puppeteerAvailable) {
            $this->warn('Puppeteer not found. Installing it might help with automatic captures.');
            $this->warn('Run: npm install puppeteer');
            return;
        }
        
        foreach ($missingBlocks as $block) {
            $pagesUsingBlock = $this->findPagesUsingBlock($block);
            
            if (!empty($pagesUsingBlock)) {
                $pageUrl = $pagesUsingBlock[0]['url'];
                $filename = public_path("img/admin/blocks/{$block}.jpg");
                
                $this->info("Attempting to capture {$block} from {$pageUrl}...");
                
                // Create a simple Node.js script for capturing
                $script = $this->generateCaptureScript($pageUrl, $filename, $block);
                $scriptPath = storage_path('app/capture-temp.js');
                
                File::put($scriptPath, $script);
                
                try {
                    $result = Process::run("node {$scriptPath}");
                    
                    if ($result->successful()) {
                        $this->info("✓ Successfully captured {$block}.jpg");
                    } else {
                        $this->error("✗ Failed to capture {$block}: " . $result->errorOutput());
                    }
                } catch (\Exception $e) {
                    $this->error("✗ Error capturing {$block}: " . $e->getMessage());
                } finally {
                    File::delete($scriptPath);
                }
            }
        }
    }
    
    private function checkPuppeteer()
    {
        try {
            $result = Process::run('node -e "require(\'puppeteer\')"');
            return $result->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
    
    private function generateCaptureScript($url, $outputPath, $blockName)
    {
        return <<<JS
const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });
    
    try {
        await page.goto('{$url}', { waitUntil: 'networkidle2' });
        
        // Wait a bit for dynamic content
        await page.waitForTimeout(2000);
        
        // Try to find the block by class or data attribute
        const blockSelector = '[data-block-type="{$blockName}"], .block-{$blockName}, .{$blockName}-block';
        
        const element = await page.\$(blockSelector);
        
        if (element) {
            // Capture just the block
            await element.screenshot({ path: '{$outputPath}' });
        } else {
            // Capture full page as fallback
            await page.screenshot({ 
                path: '{$outputPath}',
                fullPage: false,
                clip: { x: 0, y: 200, width: 1200, height: 800 }
            });
        }
        
        console.log('Screenshot saved');
    } catch (error) {
        console.error('Error:', error);
        process.exit(1);
    }
    
    await browser.close();
})();
JS;
    }
}