<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->text('paco_closing_message')->nullable()->after('paco_chat_text');
        });

        Schema::table('conversations', function (Blueprint $table): void {
            $table->foreignId('source_client_id')
                ->nullable()
                ->after('utm_campaign')
                ->constrained('clients')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('source_client_id');
        });

        Schema::table('clients', function (Blueprint $table): void {
            $table->dropColumn('paco_closing_message');
        });
    }
};
