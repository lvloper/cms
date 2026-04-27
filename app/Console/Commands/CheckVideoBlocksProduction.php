<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Page;

class CheckVideoBlocksProduction extends Command
{
    protected $signature = 'check:video-blocks-production';
    protected $description = 'Check video blocks status before production migration';

    public function handle()
    {
        $this->info('🔍 Checking video blocks status for production deployment...');
        $this->newLine();

        $totalPages = 0;
        $totalVideoBlocks = 0;
        $blocksWithVideoType = 0;
        $blocksWithoutVideoType = 0;
        $youtubeBlocks = 0;
        $uploadBlocks = 0;
        $orphanBlocks = 0;

        $detailedReport = [];

        Page::whereNotNull('blocks')->chunk(50, function ($pages) use (
            &$totalPages, &$totalVideoBlocks, &$blocksWithVideoType, &$blocksWithoutVideoType,
            &$youtubeBlocks, &$uploadBlocks, &$orphanBlocks, &$detailedReport
        ) {
            foreach ($pages as $page) {
                $totalPages++;
                $pageVideoBlocks = 0;
                $pageReport = [];

                if (is_iterable($page->blocks)) {
                    foreach ($page->blocks as $index => $block) {
                        if (isset($block['type']) && $block['type'] === 'Video') {
                            $totalVideoBlocks++;
                            $pageVideoBlocks++;
                            $data = $block['data'] ?? [];
                            
                            $videoType = $data['videoType'] ?? null;
                            $videoId = $data['videoId'] ?? null;
                            $videoFile = $data['videoFile'] ?? null;

                            $status = 'unknown';
                            
                            if (!empty($videoType)) {
                                $blocksWithVideoType++;
                                if ($videoType === 'youtube') {
                                    $youtubeBlocks++;
                                    $status = !empty($videoId) ? 'youtube-complete' : 'youtube-incomplete';
                                } elseif ($videoType === 'upload') {
                                    $uploadBlocks++;
                                    $status = !empty($videoFile) ? 'upload-complete' : 'upload-incomplete';
                                }
                            } else {
                                $blocksWithoutVideoType++;
                                if (!empty($videoId)) {
                                    $status = 'youtube-needs-migration';
                                } elseif (!empty($videoFile)) {
                                    $status = 'upload-needs-migration';
                                } else {
                                    $status = 'orphan';
                                    $orphanBlocks++;
                                }
                            }

                            $pageReport[] = [
                                'block_index' => $index,
                                'status' => $status,
                                'videoType' => $videoType,
                                'has_videoId' => !empty($videoId),
                                'has_videoFile' => !empty($videoFile),
                                'videoId' => $videoId,
                                'videoFile' => $videoFile,
                            ];
                        }
                    }
                }

                if ($pageVideoBlocks > 0) {
                    $detailedReport[] = [
                        'page_name' => $page->name ?: 'Unnamed Page',
                        'page_id' => $page->id,
                        'video_blocks_count' => $pageVideoBlocks,
                        'blocks' => $pageReport,
                    ];
                }
            }
        });

        // Summary
        $this->info("📊 SUMMARY REPORT");
        $this->table([
            'Metric', 'Count'
        ], [
            ['Total Pages', $totalPages],
            ['Total Video Blocks', $totalVideoBlocks],
            ['Blocks WITH videoType', $blocksWithVideoType],
            ['Blocks WITHOUT videoType (need migration)', $blocksWithoutVideoType],
            ['YouTube Blocks', $youtubeBlocks],
            ['Upload Blocks', $uploadBlocks],
            ['Orphan Blocks (no data)', $orphanBlocks],
        ]);

        if ($blocksWithoutVideoType > 0) {
            $this->newLine();
            $this->warn("⚠️  {$blocksWithoutVideoType} blocks need migration!");
            $this->info("Run: php artisan migrate:video-blocks --dry-run (to preview)");
            $this->info("Then: php artisan migrate:video-blocks (to migrate)");
        } else {
            $this->info("✅ All video blocks are properly configured!");
        }

        // Detailed report
        if ($this->option('verbose') || $blocksWithoutVideoType > 0) {
            $this->newLine();
            $this->info("📋 DETAILED REPORT");
            
            foreach ($detailedReport as $pageReport) {
                $this->line("📄 {$pageReport['page_name']} (ID: {$pageReport['page_id']}) - {$pageReport['video_blocks_count']} video blocks");
                
                foreach ($pageReport['blocks'] as $blockReport) {
                    $status = $blockReport['status'];
                    $icon = match($status) {
                        'youtube-complete' => '✅',
                        'upload-complete' => '✅',
                        'youtube-needs-migration' => '🔧',
                        'upload-needs-migration' => '🔧',
                        'youtube-incomplete' => '⚠️ ',
                        'upload-incomplete' => '⚠️ ',
                        'orphan' => '❌',
                        default => '❓'
                    };
                    
                    $this->line("  {$icon} Block #{$blockReport['block_index']}: {$status}");
                    if ($status === 'youtube-needs-migration') {
                        $this->line("    Will set videoType='youtube' (videoId: {$blockReport['videoId']})");
                    } elseif ($status === 'upload-needs-migration') {
                        $this->line("    Will set videoType='upload' (videoFile: {$blockReport['videoFile']})");
                    }
                }
                $this->line("");
            }
        }

        return $blocksWithoutVideoType > 0 ? 1 : 0;
    }
}
