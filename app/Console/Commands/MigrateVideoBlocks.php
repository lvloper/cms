<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Page;

class MigrateVideoBlocks extends Command
{
    protected $signature = 'migrate:video-blocks';
    protected $description = 'Add videoType field to existing video blocks for backward compatibility';

    public function handle()
    {
        $this->info('🔧 Migrating existing video blocks...');

        $pagesUpdated = 0;
        $blocksUpdated = 0;

        // Find all pages with blocks
        Page::whereNotNull('blocks')->chunk(50, function ($pages) use (&$pagesUpdated, &$blocksUpdated) {
            foreach ($pages as $page) {
                $blocks = $page->blocks;
                $pageNeedsUpdate = false;

                if (is_iterable($blocks)) {
                    foreach ($blocks as &$block) {
                        if (isset($block['type']) && $block['type'] === 'Video') {
                            $data = $block['data'] ?? [];
                            
                            // Check if videoType is missing
                            if (!isset($data['videoType']) || empty($data['videoType'])) {
                                // Auto-detect based on existing fields
                                if (!empty($data['videoId'])) {
                                    $data['videoType'] = 'youtube';
                                    $this->line("  📹 YouTube video detected: {$data['videoId']}");
                                } elseif (!empty($data['videoFile'])) {
                                    $data['videoType'] = 'upload';
                                    $this->line("  📁 Upload video detected: {$data['videoFile']}");
                                } else {
                                    $data['videoType'] = 'youtube'; // Default fallback
                                    $this->line("  🔧 Default type assigned (no video data found)");
                                }
                                
                                $block['data'] = $data;
                                $pageNeedsUpdate = true;
                                $blocksUpdated++;
                            }
                        }
                    }
                }

                if ($pageNeedsUpdate) {
                    $page->blocks = $blocks;
                    $page->save();
                    $pagesUpdated++;
                    $this->info("✅ Updated page: {$page->name}");
                }
            }
        });

        $this->info("🎉 Migration completed!");
        $this->info("📄 Pages updated: {$pagesUpdated}");
        $this->info("📹 Video blocks updated: {$blocksUpdated}");

        if ($blocksUpdated === 0) {
            $this->warn('No video blocks needed migration. All blocks already have videoType defined.');
        }
    }
}
