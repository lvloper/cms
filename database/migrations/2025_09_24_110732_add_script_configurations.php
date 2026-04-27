<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insertar configuraciones para scripts del head y body
        \App\Models\Configuration::create([
            'key' => 'head_scripts',
            'type' => 'text',
            'value' => '',
        ]);

        \App\Models\Configuration::create([
            'key' => 'body_scripts',
            'type' => 'text',
            'value' => '',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar las configuraciones creadas
        \App\Models\Configuration::where('key', 'head_scripts')->delete();
        \App\Models\Configuration::where('key', 'body_scripts')->delete();
    }
};
