<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('campaigns')
            ->whereIn('code', ['home_default', 'direct_default'])
            ->where('initial_message', '¿En qué podemos ayudarte?')
            ->update(['initial_message' => 'Hola, ¿En qué podemos ayudarte?']);
    }

    public function down(): void
    {
        DB::table('campaigns')
            ->whereIn('code', ['home_default', 'direct_default'])
            ->where('initial_message', 'Hola, ¿En qué podemos ayudarte?')
            ->update(['initial_message' => '¿En qué podemos ayudarte?']);
    }
};
