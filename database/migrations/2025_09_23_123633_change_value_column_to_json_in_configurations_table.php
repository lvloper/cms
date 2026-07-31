<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "configurations" ALTER COLUMN "value" TYPE json USING "value"::json');

            return;
        }

        Schema::table('configurations', function (Blueprint $table) {
            $table->json('value')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "configurations" ALTER COLUMN "value" TYPE text USING "value"::text');

            return;
        }

        Schema::table('configurations', function (Blueprint $table) {
            $table->text('value')->nullable()->change();
        });
    }
};
