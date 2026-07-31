<?php

declare(strict_types=1);

use App\Models\Paco\Question;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Question::query()->where('code', 'content_readiness')->first()?->forceFill([
            'options' => [
                ['value' => 'ready', 'label' => 'Sí, ya están definidos'],
                [
                    'value' => 'partial',
                    'label' => 'Tenemos una parte',
                    'allow_detail' => true,
                    'detail_label' => '¿Qué tienen preparado y qué les falta?',
                    'detail_placeholder' => 'Por ejemplo: tenemos los textos, pero nos faltan las fotos',
                ],
                [
                    'value' => 'need_help',
                    'label' => 'Necesitamos ayuda con eso',
                    'allow_detail' => true,
                    'detail_label' => '¿Con qué contenidos necesitan ayuda?',
                    'detail_placeholder' => 'Contanos brevemente qué necesitan resolver',
                ],
            ],
            'version' => 2,
        ])->save();
    }

    public function down(): void
    {
        Question::query()->where('code', 'content_readiness')->first()?->forceFill([
            'options' => [
                ['value' => 'ready', 'label' => 'Sí, ya están definidos'],
                ['value' => 'partial', 'label' => 'Tenemos una parte'],
                ['value' => 'need_help', 'label' => 'Necesitamos ayuda con eso'],
            ],
            'version' => 1,
        ])->save();
    }
};
