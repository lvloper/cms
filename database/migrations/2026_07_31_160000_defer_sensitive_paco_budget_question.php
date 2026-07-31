<?php

declare(strict_types=1);

use App\Models\Paco\Playbook;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $playbook = Playbook::query()->where('code', 'custom_system')->first();
        $playbook?->fields()->where('field_code', 'budget_context')->update(['importance' => 'medium']);
    }

    public function down(): void
    {
        $playbook = Playbook::query()->where('code', 'custom_system')->first();
        $playbook?->fields()->where('field_code', 'budget_context')->update(['importance' => 'high']);
    }
};
