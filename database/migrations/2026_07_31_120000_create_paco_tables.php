<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intents', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('commercial');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('playbooks', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('objective');
            $table->string('status')->default('active');
            $table->unsignedSmallInteger('max_interactions')->default(7);
            $table->unsignedSmallInteger('max_questions_after_contact')->default(2);
            $table->decimal('minimum_sufficiency_score', 4, 3)->default(0.700);
            $table->json('settings')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        Schema::create('intent_playbook', function (Blueprint $table): void {
            $table->foreignId('intent_id')->constrained()->cascadeOnDelete();
            $table->foreignId('playbook_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('priority')->default(100);
            $table->primary(['intent_id', 'playbook_id']);
        });

        Schema::create('questions', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('field_code');
            $table->text('prompt');
            $table->string('short_prompt')->nullable();
            $table->string('component_type');
            $table->json('options')->nullable();
            $table->boolean('is_sensitive')->default(false);
            $table->boolean('is_skippable')->default(false);
            $table->json('validation_schema')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->index(['field_code', 'status']);
        });

        Schema::create('playbook_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('playbook_id')->constrained()->cascadeOnDelete();
            $table->string('field_code');
            $table->string('importance');
            $table->json('ask_condition')->nullable();
            $table->foreignId('question_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('priority')->default(100);
            $table->timestamps();
            $table->unique(['playbook_id', 'field_code']);
        });

        Schema::create('response_blocks', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('block_type');
            $table->foreignId('intent_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stage')->nullable();
            $table->text('text');
            $table->json('allowed_variables')->nullable();
            $table->string('adaptation_mode')->default('exact');
            $table->string('status')->default('active');
            $table->unsignedSmallInteger('priority')->default(100);
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('draft');
            $table->text('initial_message');
            $table->json('context')->nullable();
            $table->foreignId('preferred_playbook_id')->nullable()->constrained('playbooks')->nullOnDelete();
            $table->foreignId('preferred_intent_id')->nullable()->constrained('intents')->nullOnDelete();
            $table->unsignedSmallInteger('max_interactions')->nullable();
            $table->json('allowed_origins')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('service_fit_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('intent_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('unknown');
            $table->json('conditions')->nullable();
            $table->foreignId('approved_response_block_id')->nullable()->constrained('response_blocks')->nullOnDelete();
            $table->json('alternative_service_ids')->nullable();
            $table->unsignedSmallInteger('priority')->default(100);
            $table->unsignedInteger('version')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('conversations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->char('public_token_hash', 64)->unique();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('playbook_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active');
            $table->string('stage')->default('new');
            $table->string('locale', 12)->default('es-AR');
            $table->text('origin_url')->nullable();
            $table->string('origin_host')->nullable();
            $table->text('referrer')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->char('client_ip_hash', 64)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('user_agent_summary', 500)->nullable();
            $table->unsignedSmallInteger('interaction_count')->default(0);
            $table->unsignedSmallInteger('useful_interaction_count')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'last_activity_at']);
        });

        Schema::create('conversation_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('actor');
            $table->string('kind');
            $table->json('payload');
            $table->uuid('idempotency_key')->nullable();
            $table->uuid('model_run_id')->nullable();
            $table->timestamps();
            $table->unique(['conversation_id', 'sequence']);
            $table->unique(['conversation_id', 'idempotency_key']);
        });

        Schema::create('leads', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status')->default('collecting');
            $table->string('name')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('role_title')->nullable();
            $table->string('contact_channel')->nullable();
            $table->text('email')->nullable();
            $table->text('phone_e164')->nullable();
            $table->string('primary_intent_code')->nullable();
            $table->string('consultation_type')->nullable();
            $table->string('fit_level')->nullable();
            $table->unsignedSmallInteger('score')->nullable();
            $table->decimal('score_confidence', 4, 3)->nullable();
            $table->string('next_action')->nullable();
            $table->text('summary')->nullable();
            $table->text('problem_summary')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('employees_range')->nullable();
            $table->string('revenue_range')->nullable();
            $table->string('decision_role')->nullable();
            $table->string('urgency')->nullable();
            $table->date('deadline')->nullable();
            $table->decimal('budget_mentioned_amount', 15, 2)->nullable();
            $table->string('budget_mentioned_currency', 3)->nullable();
            $table->json('state')->nullable();
            $table->timestamp('qualified_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'score']);
        });

        Schema::create('lead_attributes', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('lead_id')->constrained()->cascadeOnDelete();
            $table->string('field_code');
            $table->json('value_json')->nullable();
            $table->text('value_text')->nullable();
            $table->string('evidence_type');
            $table->text('evidence_text')->nullable();
            $table->decimal('confidence', 4, 3)->default(1);
            $table->foreignUuid('source_event_id')->nullable()->constrained('conversation_events')->nullOnDelete();
            $table->boolean('is_current')->default(true);
            $table->boolean('surface_to_user')->default(true);
            $table->timestamp('superseded_at')->nullable();
            $table->timestamps();
            $table->index(['lead_id', 'field_code', 'is_current']);
        });

        Schema::create('lead_scores', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('lead_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('score_total');
            $table->unsignedSmallInteger('fit_score')->default(0);
            $table->unsignedSmallInteger('clarity_score')->default(0);
            $table->unsignedSmallInteger('scale_score')->default(0);
            $table->unsignedSmallInteger('decision_score')->default(0);
            $table->unsignedSmallInteger('readiness_score')->default(0);
            $table->unsignedSmallInteger('timing_score')->default(0);
            $table->unsignedSmallInteger('interaction_score')->default(0);
            $table->string('rules_version')->default('1');
            $table->json('explanation')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_entities', function (Blueprint $table): void {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('chat_text')->nullable();
            $table->text('url')->nullable();
            $table->text('image_url')->nullable();
            $table->boolean('chat_enabled')->default(false);
            $table->boolean('published')->default(false);
            $table->string('locale', 12)->default('es-AR');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['entity_type', 'entity_id', 'locale']);
            $table->index(['published', 'chat_enabled', 'entity_type']);
        });

        Schema::create('knowledge_chunks', function (Blueprint $table): void {
            $table->id();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->string('field_path');
            $table->string('locale', 12)->default('es-AR');
            $table->text('plain_text');
            $table->char('content_hash', 64);
            $table->json('embedding')->nullable();
            $table->string('embedding_model')->nullable();
            $table->unsignedInteger('embedding_dimensions')->nullable();
            $table->string('embedding_version')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('indexed_at')->nullable();
            $table->timestamps();
            $table->unique(['source_type', 'source_id', 'field_path', 'locale', 'content_hash'], 'knowledge_chunks_source_unique');
        });

        Schema::create('content_impressions', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('event_id')->nullable()->constrained('conversation_events')->nullOnDelete();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('presentation_type')->default('compact');
            $table->unsignedSmallInteger('rank')->default(1);
            $table->string('reason_code')->nullable();
            $table->timestamps();
        });

        Schema::create('model_runs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained()->cascadeOnDelete();
            $table->string('phase');
            $table->string('provider');
            $table->string('model');
            $table->string('prompt_version');
            $table->char('input_hash', 64);
            $table->json('input_snapshot')->nullable();
            $table->json('output_snapshot')->nullable();
            $table->boolean('validated')->default(false);
            $table->json('validation_errors')->nullable();
            $table->unsignedInteger('latency_ms')->default(0);
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->decimal('cost_estimate', 12, 6)->nullable();
            $table->string('request_id')->nullable();
            $table->timestamps();
        });

        Schema::create('security_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->char('ip_hash', 64)->nullable();
            $table->string('event_type');
            $table->string('severity')->default('info');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['event_type', 'severity', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_events');
        Schema::dropIfExists('model_runs');
        Schema::dropIfExists('content_impressions');
        Schema::dropIfExists('knowledge_chunks');
        Schema::dropIfExists('knowledge_entities');
        Schema::dropIfExists('lead_scores');
        Schema::dropIfExists('lead_attributes');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('conversation_events');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('service_fit_rules');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('response_blocks');
        Schema::dropIfExists('playbook_fields');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('intent_playbook');
        Schema::dropIfExists('playbooks');
        Schema::dropIfExists('intents');
    }
};
