<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('clients')
            ->select(['id', 'testimonials', 'preview_items'])
            ->orderBy('id')
            ->each(function (object $client): void {
                $items = json_decode($client->preview_items ?? '[]', true) ?: [];
                $testimonials = json_decode($client->testimonials ?? '[]', true) ?: [];

                $items = collect($items)
                    ->map(function (array $item): array {
                        if (isset($item['type'])) {
                            return $item;
                        }

                        $extension = strtolower(pathinfo((string) ($item['file'] ?? ''), PATHINFO_EXTENSION));
                        $item['type'] = in_array($extension, ['mp4', 'webm', 'mov'], true) ? 'video' : 'image';

                        return $item;
                    })
                    ->values()
                    ->all();

                $hasTestimonial = collect($testimonials)
                    ->contains(fn (mixed $item): bool => is_array($item) && filled(strip_tags((string) ($item['testimonial'] ?? ''))));
                $hasTestimonialChannel = collect($items)
                    ->contains(fn (array $item): bool => ($item['type'] ?? null) === 'testimonial');

                if ($hasTestimonial && ! $hasTestimonialChannel) {
                    array_unshift($items, [
                        'type' => 'testimonial',
                        'duration_ms' => null,
                    ]);
                }

                DB::table('clients')
                    ->where('id', $client->id)
                    ->update(['preview_items' => json_encode($items)]);
            });
    }

    public function down(): void
    {
        DB::table('clients')
            ->select(['id', 'preview_items'])
            ->orderBy('id')
            ->each(function (object $client): void {
                $items = collect(json_decode($client->preview_items ?? '[]', true) ?: [])
                    ->reject(fn (array $item): bool => ($item['type'] ?? null) === 'testimonial')
                    ->map(function (array $item): array {
                        unset($item['type']);

                        return $item;
                    })
                    ->values()
                    ->all();

                DB::table('clients')
                    ->where('id', $client->id)
                    ->update(['preview_items' => json_encode($items)]);
            });
    }
};
