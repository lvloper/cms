<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Tags\Tag;

class CleanDuplicateTagsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:duplicate-tags
                            {--dry-run : Show what would be deleted without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia tags duplicados (minúscula vs capitalizada) manteniendo solo la versión capitalizada';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->info('🔍 Buscando tags duplicados...');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('⚠️  Modo DRY-RUN: No se realizarán cambios');
            $this->newLine();
        }

        $allTags = Tag::all();
        $tagsToDelete = [];
        $tagGroups = [];

        // Group tags by lowercase version
        foreach ($allTags as $tag) {
            $lowerName = mb_strtolower($tag->name);
            
            if (!isset($tagGroups[$lowerName])) {
                $tagGroups[$lowerName] = [];
            }
            
            $tagGroups[$lowerName][] = $tag;
        }

        // Find duplicates
        foreach ($tagGroups as $lowerName => $tags) {
            if (count($tags) <= 1) {
                continue; // No duplicates
            }

            // Sort: prioritize capitalized versions
            usort($tags, function($a, $b) {
                // If one is capitalized and the other isn't, keep the capitalized one
                $aCapitalized = $this->isCapitalized($a->name);
                $bCapitalized = $this->isCapitalized($b->name);
                
                if ($aCapitalized && !$bCapitalized) return -1;
                if (!$aCapitalized && $bCapitalized) return 1;
                
                // If both or neither are capitalized, prefer the one with more usage
                return $this->getTagUsageCount($b) <=> $this->getTagUsageCount($a);
            });

            // Keep the first one (preferred), delete the rest
            $keepTag = array_shift($tags);
            
            foreach ($tags as $duplicateTag) {
                $tagsToDelete[] = [
                    'delete' => $duplicateTag,
                    'keep' => $keepTag,
                ];
            }
        }

        if (empty($tagsToDelete)) {
            $this->info('✅ No se encontraron tags duplicados.');
            return Command::SUCCESS;
        }

        $this->warn("⚠️  Se encontraron " . count($tagsToDelete) . " tags duplicados:");
        $this->newLine();

        foreach ($tagsToDelete as $item) {
            $deleteTag = $item['delete'];
            $keepTag = $item['keep'];
            $usageCount = $this->getTagUsageCount($deleteTag);

            $this->line("  📝 Tag: \"{$deleteTag->name}\" → mantener \"{$keepTag->name}\"");
            $this->line("     Usos: {$usageCount}");
            
            if (!$isDryRun) {
                // Get all models using this tag
                $this->migrateTagUsage($deleteTag, $keepTag);
                
                // Delete the duplicate tag
                $deleteTag->delete();
                $this->line("     ✓ Eliminado y migrado");
            }
            
            $this->newLine();
        }

        if ($isDryRun) {
            $this->comment('💡 Ejecuta sin --dry-run para aplicar los cambios.');
        } else {
            $this->info("✅ Se limpiaron " . count($tagsToDelete) . " tags duplicados.");
        }

        return Command::SUCCESS;
    }

    /**
     * Check if a tag name is capitalized
     */
    protected function isCapitalized(string $name): bool
    {
        $firstChar = mb_substr($name, 0, 1);
        return $firstChar === mb_strtoupper($firstChar) && ctype_alpha($firstChar);
    }

    /**
     * Get the usage count of a tag
     */
    protected function getTagUsageCount(Tag $tag): int
    {
        return DB::table('taggables')
            ->where('tag_id', $tag->id)
            ->count();
    }

    /**
     * Migrate all usages from old tag to new tag
     */
    protected function migrateTagUsage(Tag $oldTag, Tag $newTag): void
    {
        // Get all records using the old tag
        $usages = DB::table('taggables')
            ->where('tag_id', $oldTag->id)
            ->get();

        foreach ($usages as $usage) {
            // Check if the new tag is already attached to this taggable
            $exists = DB::table('taggables')
                ->where('tag_id', $newTag->id)
                ->where('taggable_type', $usage->taggable_type)
                ->where('taggable_id', $usage->taggable_id)
                ->exists();

            if (!$exists) {
                // Update to use the new tag
                DB::table('taggables')
                    ->where('tag_id', $oldTag->id)
                    ->where('taggable_type', $usage->taggable_type)
                    ->where('taggable_id', $usage->taggable_id)
                    ->update(['tag_id' => $newTag->id]);
            } else {
                // If already exists, just delete the old one
                DB::table('taggables')
                    ->where('tag_id', $oldTag->id)
                    ->where('taggable_type', $usage->taggable_type)
                    ->where('taggable_id', $usage->taggable_id)
                    ->delete();
            }
        }
    }
}
