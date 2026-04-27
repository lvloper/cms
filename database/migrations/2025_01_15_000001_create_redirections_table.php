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
        Schema::create('redirections', function (Blueprint $table) {
            $table->id();
            $table->string('old_url')->comment('URL de origen (relativa)');
            $table->string('new_url')->nullable()->comment('URL de destino (relativa o absoluta) - nullable para modo borrador');
            $table->integer('redirect_code')->default(301)->comment('Código de redirección HTTP');
            $table->boolean('is_active')->default(true)->comment('Estado activo/inactivo');
            $table->text('description')->nullable()->comment('Descripción opcional');
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index('old_url');
            $table->index('is_active');
            $table->index(['old_url', 'is_active']);
            
            // Asegurar que no haya URLs de origen duplicadas
            $table->unique('old_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redirections');
    }
};