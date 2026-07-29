<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->default(0)->after('id');
        });

        DB::table('clients')
            ->orderBy('id')
            ->get(['id'])
            ->each(function (object $client, int $index): void {
                DB::table('clients')
                    ->where('id', $client->id)
                    ->update(['sort_order' => $index]);
            });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropColumn('sort_order');
        });
    }
};
