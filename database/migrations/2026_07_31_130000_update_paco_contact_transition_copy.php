<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_TEXT = 'Perfecto. Guardemos este contexto para poder revisarlo con el equipo.';

    private const NEW_TEXT = 'Genial, te molestamos con algunos datos para entenderlo mejor.';

    public function up(): void
    {
        DB::table('response_blocks')
            ->where('code', 'contact_transition_default')
            ->where('text', self::OLD_TEXT)
            ->update(['text' => self::NEW_TEXT, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('response_blocks')
            ->where('code', 'contact_transition_default')
            ->where('text', self::NEW_TEXT)
            ->update(['text' => self::OLD_TEXT, 'updated_at' => now()]);
    }
};
