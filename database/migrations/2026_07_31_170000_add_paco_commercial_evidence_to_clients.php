<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->string('public_name')->nullable();
            $table->string('industry')->nullable();
            $table->text('paco_summary')->nullable();
            $table->text('paco_chat_text')->nullable();
            $table->boolean('paco_use_authorized')->default(false);
            $table->boolean('paco_chat_enabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropColumn([
                'public_name',
                'industry',
                'paco_summary',
                'paco_chat_text',
                'paco_use_authorized',
                'paco_chat_enabled',
            ]);
        });
    }
};
