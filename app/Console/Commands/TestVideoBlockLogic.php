<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestVideoBlockLogic extends Command
{
    protected $signature = 'test:video-logic';
    protected $description = 'Test video block auto-detection logic';

    public function handle()
    {
        $this->info('🧪 Testing video block detection logic...');

        // Simular el bloque que mostraste
        $blockData = [
            "mb" => "mb-0",
            "mdMb" => "md:mb-0",
            "style" => "compact",
            "clases" => [],
            "hidden" => false,
            "styles" => [],
            "videoId" => "bOGtBerXzBc",
            "stylesMd" => [],
            "blockTitle" => null,
            "blockAnchor" => null
        ];

        // Simular la lógica de la vista
        $videoType = null;
        $videoId = $blockData['videoId'] ?? null;
        $videoFile = $blockData['videoFile'] ?? null;

        $this->line('📋 Input data:');
        $this->line('  videoType: ' . ($blockData['videoType'] ?? 'undefined'));
        $this->line('  videoId: ' . ($videoId ?: 'undefined'));
        $this->line('  videoFile: ' . ($videoFile ?: 'undefined'));
        
        // Auto-detectar tipo de video si no está definido
        if (!isset($videoType) || empty($videoType)) {
            if (!empty($videoId)) {
                $videoType = 'youtube';
                $this->info('✅ Auto-detected: YouTube video');
            } elseif (!empty($videoFile)) {
                $videoType = 'upload';
                $this->info('✅ Auto-detected: Upload video');
            } else {
                $videoType = 'youtube'; // Default fallback
                $this->warn('⚠️  No data found, using default: YouTube');
            }
        }

        $this->line('');
        $this->line('🎯 Final result:');
        $this->line('  Final videoType: ' . $videoType);
        
        if ($videoType === 'youtube' && !empty($videoId)) {
            $this->info('  ✅ Will show YouTube video with ID: ' . $videoId);
            
            // Test YouTube ID extraction
            if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\s*(?:\w*\/)*|(?:v|e(?:mbed)?)\/|\w*v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', 
            $videoId, $matches)) {
                $cleanId = $matches[1];
                $this->line('  📹 Extracted YouTube ID: ' . $cleanId);
            } else {
                $this->line('  📹 Direct YouTube ID: ' . $videoId);
            }
        } elseif ($videoType === 'upload' && !empty($videoFile)) {
            $this->info('  ✅ Will show uploaded video: ' . $videoFile);
        } else {
            $this->error('  ❌ No video will be shown - missing data');
        }
    }
}
