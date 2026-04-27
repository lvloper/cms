<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Page;

class VerifyVideoBlocks extends Command
{
    protected $signature = 'verify:video-blocks';
    protected $description = 'Verify that video blocks are properly configured after migration';

    public function handle()
    {
        $this->info('🔍 Verifying video blocks configuration...');

        $totalBlocks = 0;
        $youtubeBlocks = 0;
        $uploadBlocks = 0;
        $incompleteBlocks = 0;

        Page::whereNotNull('blocks')->chunk(50, function ($pages) use (&$totalBlocks, &$youtubeBlocks, &$uploadBlocks, &$incompleteBlocks) {
            foreach ($pages as $page) {
                $blocks = $page->blocks;

                if (is_iterable($blocks)) {
                    foreach ($blocks as $index => $block) {
                        if (isset($block['type']) && $block['type'] === 'Video') {
                            $totalBlocks++;
                            $data = $block['data'] ?? [];
                            
                            $videoType = $data['videoType'] ?? 'unknown';
                            $videoId = $data['videoId'] ?? null;
                            $videoFile = $data['videoFile'] ?? null;

                            $this->line("📄 {$page->name} - Block #{$index}:");
                            $this->line("  Type: {$videoType}");

                            if ($videoType === 'youtube') {
                                $youtubeBlocks++;
                                if (!empty($videoId)) {
                                    $this->line("  ✅ YouTube ID: {$videoId}");
                                } else {
                                    $this->error("  ❌ Missing YouTube ID!");
                                    $incompleteBlocks++;
                                }
                            } elseif ($videoType === 'upload') {
                                $uploadBlocks++;
                                if (!empty($videoFile)) {
                                    $this->line("  ✅ Video file: {$videoFile}");
                                } else {
                                    $this->error("  ❌ Missing video file!");
                                    $incompleteBlocks++;
                                }
                            } else {
                                $this->warn("  ⚠️  Unknown video type: {$videoType}");
                                $incompleteBlocks++;
                            }

                            $this->line("");
                        }
                    }
                }
            }
        });

        $this->info("📊 Verification Summary:");
        $this->info("  Total video blocks: {$totalBlocks}");
        $this->info("  YouTube blocks: {$youtubeBlocks}");
        $this->info("  Upload blocks: {$uploadBlocks}");
        
        if ($incompleteBlocks > 0) {
            $this->error("  ❌ Incomplete blocks: {$incompleteBlocks}");
        } else {
            $this->info("  ✅ All blocks are properly configured!");
        }
    }
}
