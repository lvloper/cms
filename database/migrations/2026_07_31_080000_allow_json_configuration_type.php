<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE "configurations" DROP CONSTRAINT IF EXISTS "configurations_type_check"');
        DB::statement(<<<'SQL'
            ALTER TABLE "configurations"
            ADD CONSTRAINT "configurations_type_check"
            CHECK ("type" IN ('text', 'rich_text', 'url', 'image', 'json'))
        SQL);
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::table('configurations')
            ->where('type', 'json')
            ->update(['type' => 'text']);

        DB::statement('ALTER TABLE "configurations" DROP CONSTRAINT IF EXISTS "configurations_type_check"');
        DB::statement(<<<'SQL'
            ALTER TABLE "configurations"
            ADD CONSTRAINT "configurations_type_check"
            CHECK ("type" IN ('text', 'rich_text', 'url', 'image'))
        SQL);
    }
};
