<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('clients')
            ->select(['id', 'color'])
            ->orderBy('id')
            ->each(function (object $client): void {
                DB::table('clients')
                    ->where('id', $client->id)
                    ->update([
                        'popup_text_color' => $this->preferredTextColor($client->color),
                    ]);
            });
    }

    public function down(): void
    {
        // The field is removed by the preceding schema migration.
    }

    private function preferredTextColor(?string $color): string
    {
        if (! is_string($color) || ! preg_match('/^#?([0-9a-f]{6})$/i', $color, $matches)) {
            return 'white';
        }

        $hex = $matches[1];
        $channels = array_map(
            fn (int $offset): float => $this->linearChannel(hexdec(substr($hex, $offset, 2)) / 255),
            [0, 2, 4],
        );
        $luminance = 0.2126 * $channels[0]
            + 0.7152 * $channels[1]
            + 0.0722 * $channels[2];
        $blackContrast = ($luminance + 0.05) / 0.05;
        $whiteContrast = 1.05 / ($luminance + 0.05);

        return $blackContrast >= $whiteContrast ? 'black' : 'white';
    }

    private function linearChannel(float $channel): float
    {
        return $channel <= 0.04045
            ? $channel / 12.92
            : (($channel + 0.055) / 1.055) ** 2.4;
    }
};
