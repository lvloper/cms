<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Page;

class FixVideoBlocksMigration extends Command
{
    protected $signature = 'fix:video-blocks';
    protected $description = 'Fix video blocks by adding missing videoType fields';

    public function handle()
    {
        $this->info('🔧 Fixing video blocks with missing videoType...');

        $pagesUpdated = 0;
        $blocksFixed = 0;

        Page::whereNotNull('blocks')->chunk(50, function ($pages) use (&$pagesUpdated, &$blocksFixed) {
            foreach ($pages as $page) {
                $blocks = $page->blocks->toArray(); // Convert to array to ensure mutability
                $pageModified = false;

                foreach ($blocks as $index => &$block) {
                    if (isset($block['type']) && $block['type'] === 'Video') {
                        $data = $block['data'] ?? [];
                        
                        // Check if videoType is missing (not set or null)
                        $hasVideoType = array_key_exists('videoType', $data) && $data['videoType'] !== null && $data['videoType'] !== '';
                        
                        if (!$hasVideoType) {
                            // Auto-detect based on existing fields
                            if (!empty($data['videoId'])) {
                                $data['videoType'] = 'youtube';
                                $this->line("  📹 Fixed Block #{$index} in Page {$page->id}: YouTube ({$data['videoId']})");
                            } elseif (!empty($data['videoFile'])) {
                                $data['videoType'] = 'upload';
                                $this->line("  📁 Fixed Block #{$index} in Page {$page->id}: Upload ({$data['videoFile']})");
                            } else {
                                $data['videoType'] = 'youtube'; // Default fallback
                                $this->line("  🔧 Fixed Block #{$index} in Page {$page->id}: Default (no data)");
                            }
                            
                            $block['data'] = $data;
                            $pageModified = true;
                            $blocksFixed++;
                        } else {
                            $this->line("  ✅ Block #{$index} in Page {$page->id}: Already has videoType '{$data['videoType']}'");
                        }
                    }
                }

                if ($pageModified) {
                    $page->blocks = collect($blocks);
                    $page->save();
                    $pagesUpdated++;
                    $pageName = $page->name ?: 'Unnamed';
                    $this->info("✅ Updated Page {$page->id} ({$pageName})");
                }
            }
        });

        $this->info("🎉 Fix completed!");
        $this->info("📄 Pages updated: {$pagesUpdated}");
        $this->info("🔧 Blocks fixed: {$blocksFixed}");

        return $blocksFixed === 0 ? 0 : 1;
    }
}
