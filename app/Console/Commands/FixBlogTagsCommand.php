<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Blog;

class FixBlogTagsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:blog-tags
                            {--dry-run : Show what would be changed without making changes}
                            {--tag= : Fix only specific tag (e.g., ciencia, derechos)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cambia tags de novedades en minúscula a capitalización correcta (primera letra mayúscula)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $specificTag = $this->option('tag');

        $this->info('🔍 Buscando tags en minúscula en Novedades...');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('⚠️  Modo DRY-RUN: No se realizarán cambios');
            $this->newLine();
        }

        // Get all blogs
        // Get all blogs
        $blogs = Blog::all();
        $totalBlogs = 0;
        $totalTagsChanged = 0;
        $tagsToDelete = []; // Track old tags to delete

        foreach ($blogs as $blog) {
            // Get tags for this blog
            $tags = $blog->tags;
            
            if ($tags->isEmpty()) {
                continue;
            }
            
            $changedTags = [];
            $hasChanges = false;

            foreach ($tags as $tag) {
                $originalName = $tag->name;
                
                // Skip if specific tag filter is set and doesn't match
                if ($specificTag && strtolower($originalName) !== strtolower($specificTag)) {
                    continue;
                }

                // Check if tag is all lowercase (excluding special characters)
                if ($this->shouldCapitalize($originalName)) {
                    $newName = $this->capitalize($originalName);
                    
                    if ($newName !== $originalName) {
                        $changedTags[] = [
                            'old' => $originalName,
                            'old_slug' => $tag->slug,
                            'new' => $newName
                        ];
                        $hasChanges = true;
                        $totalTagsChanged++;
                        
                        // Track old tag for deletion
                        if (!$isDryRun && !isset($tagsToDelete[$tag->slug])) {
                            $tagsToDelete[$tag->slug] = $tag;
                        }
                    }
                }
            }

            if ($hasChanges) {
                $totalBlogs++;
                $this->displayChanges($blog, $changedTags, $isDryRun);

                if (!$isDryRun) {
                    // Remove old tags and add new ones
                    $newTags = [];
                    foreach ($tags as $tag) {
                        $found = false;
                        foreach ($changedTags as $change) {
                            if ($tag->name === $change['old']) {
                                $newTags[] = $change['new'];
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $newTags[] = $tag->name;
                        }
                    }
                    
                    $blog->syncTags($newTags);
                }
            }
        }

        // Delete old tags after migration
        if (!$isDryRun && !empty($tagsToDelete)) {
            $this->newLine();
            $this->info('🗑️  Eliminando tags antiguos en minúscula...');
            
            foreach ($tagsToDelete as $tag) {
                $tag->delete();
                $this->line("  ✓ Eliminado: \"{$tag->name}\"");
            }
        }

        $this->newLine();

        if ($totalBlogs === 0) {
            $this->info('✅ No se encontraron tags para corregir.');
        } else {
            if ($isDryRun) {
                $this->warn("📊 Se encontraron {$totalTagsChanged} tags en {$totalBlogs} novedades que necesitan corrección.");
                $this->comment('💡 Ejecuta sin --dry-run para aplicar los cambios.');
            } else {
                $this->info("✅ Se corrigieron {$totalTagsChanged} tags en {$totalBlogs} novedades.");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Determine if a tag should be capitalized
     */
    protected function shouldCapitalize(string $tag): bool
    {
        // Check if the first letter is lowercase
        $firstChar = mb_substr($tag, 0, 1);
        return $firstChar !== mb_strtoupper($firstChar) && ctype_alpha($firstChar);
    }

    /**
     * Capitalize tag name (first letter uppercase, rest lowercase)
     */
    protected function capitalize(string $tag): string
    {
        return mb_convert_case($tag, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Display changes for a blog
     */
    protected function displayChanges(Blog $blog, array $changedTags, bool $isDryRun): void
    {
        $title = $blog->route->title ?? 'Sin título';
        $icon = $isDryRun ? '📝' : '✓';
        
        $this->line("  {$icon} Blog: {$title}");
        
        foreach ($changedTags as $change) {
            $this->line("     • \"{$change['old']}\" → \"{$change['new']}\"");
        }
        
        if (isset($blog->route)) {
            $this->line("     🌐 " . url($blog->route->getFullPath()));
        }
        
        $this->newLine();
    }
}
