<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->string('hero_eyebrow')->nullable();
            $table->string('hero_title')->nullable();
            $table->text('hero_summary')->nullable();
            $table->string('relationship_since')->nullable();
            $table->json('hero_services')->nullable();
            $table->string('hero_media_type')->nullable();
            $table->string('hero_media_image')->nullable();
            $table->string('hero_media_video')->nullable();
            $table->string('hero_media_alt', 300)->nullable();
            $table->string('hero_media_placeholder')->nullable();
            $table->boolean('hero_media_autoplay')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropColumn([
                'hero_eyebrow',
                'hero_title',
                'hero_summary',
                'relationship_since',
                'hero_services',
                'hero_media_type',
                'hero_media_image',
                'hero_media_video',
                'hero_media_alt',
                'hero_media_placeholder',
                'hero_media_autoplay',
            ]);
        });
    }
};
