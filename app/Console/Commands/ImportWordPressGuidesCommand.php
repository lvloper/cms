<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Material;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ImportWordPressGuidesCommand extends Command
{
    protected $signature = 'import:wordpress-guides {--test} {--clean} {--force}';
    protected $description = 'Import guides (dlm_download type) from WordPress database';

    public function handle()
    {
        $this->info('Starting WordPress Guides Import...');
        
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
            $this->cleanExistingMaterials();
        }
        
        $guides = $this->getWordPressGuides();
        
        if (empty($guides)) {
            $this->warn('No guides found in WordPress database');
            return 0;
        }
        
        $this->info("Found " . count($guides) . " guides to process");
        
        $stats = [
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0
        ];
        
        foreach ($guides as $guideData) {
            $stats['processed']++;
            
            try {
                if ($isTest) {
                    $this->displayGuideInfo($guideData);
                    continue;
                }
                
                $result = $this->processGuide($guideData, $shouldForce);
                $stats[$result]++;
                
            } catch (\Exception $e) {
                $stats['errors']++;
                $this->error("Error processing guide '{$guideData['title']}': " . $e->getMessage());
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
    
    private function cleanExistingMaterials(): void
    {
        $this->info('Cleaning existing materials...');
        
        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Set material_id to null in subscribers table
            DB::table('subscribers')->update(['material_id' => null]);
            
            // Truncate materials table
            DB::table('materials')->truncate();
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            $this->info('✓ Existing materials cleaned successfully');
            
        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            throw new \Exception('Failed to clean existing materials: ' . $e->getMessage());
        }
    }
    
    private function getWordPressGuides(): array
    {
        $this->info('Fetching guides from WordPress database...');
        
        $query = "
            SELECT 
                p.ID as wp_id,
                p.post_title as title,
                p.post_content as content,
                p.post_excerpt as excerpt,
                p.post_date,
                p.post_status,
                GROUP_CONCAT(CASE WHEN pm.meta_key = '_thumbnail_id' THEN pm.meta_value END) as thumbnail_id,
                GROUP_CONCAT(CASE WHEN pm.meta_key = '_dlm_file_id' THEN pm.meta_value END) as file_id,
                GROUP_CONCAT(CASE WHEN pm.meta_key = '_download_count' THEN pm.meta_value END) as download_count
            FROM wp_posts p
            LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id
            WHERE p.post_type = 'dlm_download'
            AND p.post_status = 'publish'
            GROUP BY p.ID, p.post_title, p.post_content, p.post_excerpt, p.post_date, p.post_status
            ORDER BY p.post_date DESC
        ";
        
        $results = DB::connection('wordpress')->select($query);
        
        $guides = [];
        
        foreach ($results as $row) {
            $guideData = $this->processGuideData($row);
            if ($guideData) {
                $guides[] = $guideData;
            }
        }
        
        return $guides;
    }
    
    private function processGuideData($row): ?array
    {
        // Clean description from HTML
        $description = $row->content ?: $row->excerpt ?: '';
        $description = strip_tags($description);
        $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
        $description = trim($description);
        
        // Get image URL if thumbnail exists
        $imageUrl = null;
        if ($row->thumbnail_id) {
            $imageUrl = $this->getImageUrl($row->thumbnail_id);
        } else {
            // Try alternative image search when no thumbnail_id
            $imageUrl = $this->findAlternativeImage($row->wp_id, $row->title);
        }
        
        // Get PDF file URL
        $pdfUrl = $this->getPdfUrl($row->wp_id, $row->file_id);
        
        if (!$pdfUrl) {
            $this->warn("No PDF found for guide: {$row->title}");
            return null;
        }
        
        // Set published date
        $publishedAt = null;
        if ($row->post_status === 'publish' && $row->post_date) {
            try {
                $publishedAt = Carbon::parse($row->post_date);
            } catch (\Exception $e) {
                $publishedAt = now();
            }
        } else {
            $publishedAt = now();
        }
        
        return [
            'wp_id' => $row->wp_id,
            'title' => $row->title,
            'description' => $description,
            'image' => $imageUrl,
            'pdf_file' => $pdfUrl,
            'published_at' => $publishedAt,
            'download_count' => $row->download_count ?: 0
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
    
    private function findAlternativeImage($wpId, $title): ?string
    {
        try {
            // Special case for post 28442 - look for the specific image first
            if ($wpId == 28442) {
                $specificImage = DB::connection('wordpress')->select(
                    "SELECT p.guid 
                     FROM wp_posts p 
                     WHERE p.post_type = 'attachment' AND p.post_mime_type LIKE 'image/%'
                     AND LOWER(p.post_title) LIKE '%sphere_informetapa_mini%'
                     ORDER BY p.ID DESC LIMIT 1"
                );
                
                if (!empty($specificImage)) {
                    return $specificImage[0]->guid;
                }
            }
            
            // First, try to find attachments that are children of this post
            $childAttachments = DB::connection('wordpress')->select(
                "SELECT p.guid, p.post_title 
                 FROM wp_posts p 
                 WHERE p.post_parent = ? AND p.post_type = 'attachment' AND p.post_mime_type LIKE 'image/%'
                 ORDER BY p.ID DESC LIMIT 1",
                [$wpId]
            );
            
            if (!empty($childAttachments)) {
                return $childAttachments[0]->guid;
            }
            
            // Second, try to find images with similar names
            $cleanTitle = preg_replace('/[^a-zA-Z0-9\s]/', '', $title);
            $titleWords = explode(' ', strtolower($cleanTitle));
            
            // Look for attachments with titles containing key words from the post title
            foreach ($titleWords as $word) {
                if (strlen($word) > 3) { // Only search for words longer than 3 characters
                    $similarImages = DB::connection('wordpress')->select(
                        "SELECT p.guid, p.post_title 
                         FROM wp_posts p 
                         WHERE p.post_type = 'attachment' AND p.post_mime_type LIKE 'image/%'
                         AND LOWER(p.post_title) LIKE ?
                         ORDER BY p.ID DESC LIMIT 1",
                        ['%' . $word . '%']
                    );
                    
                    if (!empty($similarImages)) {
                        return $similarImages[0]->guid;
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->warn("Error finding alternative image for post {$wpId}: " . $e->getMessage());
        }
        
        return null;
    }
    
    private function getPdfUrl($wpId, $fileId = null): ?string
    {
        // First try to get from file_id if available
        if ($fileId) {
            try {
                $file = DB::connection('wordpress')->select(
                    "SELECT guid FROM wp_posts WHERE ID = ? AND post_type = 'attachment'",
                    [$fileId]
                );
                
                if (!empty($file)) {
                    return $file[0]->guid;
                }
            } catch (\Exception $e) {
                $this->warn("Error getting file for file ID {$fileId}: " . $e->getMessage());
            }
        }
        
        // Try to find PDF attachments for this post
        try {
            $attachments = DB::connection('wordpress')->select(
                "SELECT guid FROM wp_posts WHERE post_parent = ? AND post_type = 'attachment' AND post_mime_type = 'application/pdf' ORDER BY ID DESC LIMIT 1",
                [$wpId]
            );
            
            if (!empty($attachments)) {
                return $attachments[0]->guid;
            }
        } catch (\Exception $e) {
            $this->warn("Error getting PDF attachment for post {$wpId}: " . $e->getMessage());
        }
        
        // Fallback: use download URL format
        return "https://huesped.org.ar/descargas/{$wpId}/";
    }
    
    private function processGuide(array $guideData, bool $shouldForce): string
    {
        // Check if material already exists
        $existingMaterial = Material::where('wp_id', $guideData['wp_id'])->first();
        
        if ($existingMaterial) {
            if (!$shouldForce) {
                $this->line("Skipping existing guide: {$guideData['title']}");
                return 'skipped';
            }
            
            // Update existing material
            $existingMaterial->update([
                'title' => $guideData['title'],
                'description' => $guideData['description'],
                'image' => $guideData['image'],
                'pdf_file' => $guideData['pdf_file'],
                'published_at' => $guideData['published_at']
            ]);
            
            $this->line("Updated guide: {$guideData['title']}");
            return 'updated';
        }
        
        // Create new material
        Material::create([
            'wp_id' => $guideData['wp_id'],
            'title' => $guideData['title'],
            'description' => $guideData['description'],
            'image' => $guideData['image'],
            'pdf_file' => $guideData['pdf_file'],
            'published_at' => $guideData['published_at']
        ]);
        
        $this->line("Created guide: {$guideData['title']}");
        return 'created';
    }
    
    private function displayGuideInfo(array $guideData): void
    {
        $this->info("Guide: {$guideData['title']}");
        $this->line("  WP ID: {$guideData['wp_id']}");
        $this->line("  Description: " . Str::limit($guideData['description'], 100));
        $this->line("  Image: " . ($guideData['image'] ?: 'None'));
        $this->line("  PDF: {$guideData['pdf_file']}");
        $this->line("  Published: {$guideData['published_at']}");
        $this->line('');
    }
    
    private function displayStats(array $stats, bool $isTest): void
    {
        $this->info('');
        $this->info('=== Import Summary ===');
        
        if ($isTest) {
            $this->info("Processed: {$stats['processed']} guides (TEST MODE)");
        } else {
            $this->info("Processed: {$stats['processed']} guides");
            $this->info("Created: {$stats['created']}");
            $this->info("Updated: {$stats['updated']}");
            $this->info("Skipped: {$stats['skipped']}");
            $this->info("Errors: {$stats['errors']}");
        }
    }
}