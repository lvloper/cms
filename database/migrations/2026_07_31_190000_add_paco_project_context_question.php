<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $questionId = DB::table('questions')->where('code', 'project_context')->value('id');
        if (! $questionId) {
            $questionId = DB::table('questions')->insertGetId([
                'code' => 'project_context',
                'field_code' => 'project_context',
                'prompt' => '¿Para qué tipo de negocio u organización es la web y qué debería poder hacer?',
                'component_type' => 'text_input',
                'is_sensitive' => false,
                'is_skippable' => false,
                'validation_schema' => json_encode(['max_length' => 1500]),
                'status' => 'active',
                'version' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $playbookId = DB::table('playbooks')->where('code', 'landing_project')->value('id');
        if ($playbookId) {
            DB::table('playbook_fields')->updateOrInsert(
                ['playbook_id' => $playbookId, 'field_code' => 'project_context'],
                [
                    'importance' => 'medium',
                    'question_id' => $questionId,
                    'priority' => 35,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        $questionId = DB::table('questions')->where('code', 'project_context')->value('id');
        if ($questionId) {
            DB::table('playbook_fields')->where('question_id', $questionId)->delete();
            DB::table('questions')->where('id', $questionId)->delete();
        }
    }
};
