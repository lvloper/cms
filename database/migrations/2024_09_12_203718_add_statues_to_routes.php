<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\Status;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('routes', 'status')) {
            Schema::table('routes', function (Blueprint $table) {
                $table->string('status')
                      ->check("status in ('draft', 'reviewing', 'published', 'rejected')")
                      ->default('draft')
                      ->nullable(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            //
            $table->dropColumn('status');
        });
    }
};
