<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Paco\ResponseBlock;
use Illuminate\Database\Seeder;

final class PacoResponseBlockSeeder extends Seeder
{
    public function run(): void
    {
        $blocks = [
            ['acknowledgement_default', 'acknowledgement', 'Entendimos: {need_short}.'],
            ['contact_transition_default', 'contact_transition', 'Ya tenemos un buen punto de partida. Para continuar y poder responderte, compartinos tus datos de contacto.'],
            ['experience_intro_default', 'experience_intro', 'Tenemos experiencia relacionada que puede ayudarte a entender cómo trabajamos.'],
            ['no_evidence_default', 'experience_intro', 'Vamos a analizar cuál es el mejor enfoque para este caso y retomarlo con vos.'],
            ['qualification_transition_default', 'qualification_transition', 'Para orientarlo mejor, necesitamos saber {question_reason}.'],
            ['price_policy_default', 'price_policy', 'Para presupuestarlo necesitamos entender un poco mejor el proyecto. El equipo lo va a revisar con este contexto.'],
            ['time_policy_default', 'time_policy', 'El plazo depende del alcance y del material disponible. Primero necesitamos entender esos puntos.'],
            ['unsupported_default', 'unsupported', 'Gracias por escribirnos. Ese tipo de consulta no forma parte de los servicios que ofrecemos actualmente.'],
            ['unknown_fit_default', 'clarification', 'Queremos entenderlo un poco mejor antes de decirte si podemos ayudarte.'],
            ['off_topic_first', 'off_topic', 'Esta conversación está pensada para proyectos y servicios de Socies. Volvamos a lo que necesitan resolver.'],
            ['off_topic_close', 'closing', 'No pudimos reunir información suficiente sobre una consulta para Socies. Cerramos esta conversación por ahora.'],
            ['closing_sufficient', 'closing', 'Gracias, {name}. Ya tenemos la información necesaria. Nuestro equipo va a revisar el caso y te va a contactar por {channel}.'],
            ['closing_low_information', 'closing', 'Gracias por escribirnos. Con la información disponible no podemos avanzar por ahora.'],
            ['technical_error', 'error', 'Tuvimos un problema para procesar eso. Probemos nuevamente.'],
            ['rate_limited', 'error', 'Alcanzamos el límite de esta conversación. Podés intentarlo nuevamente más tarde.'],
        ];

        foreach ($blocks as [$code, $type, $text]) {
            ResponseBlock::query()->firstOrCreate(
                ['code' => $code],
                [
                    'block_type' => $type,
                    'text' => $text,
                    'allowed_variables' => ['need_short', 'question_reason', 'name', 'channel'],
                    'adaptation_mode' => 'tone_and_length',
                    'status' => 'active',
                    'priority' => 100,
                    'version' => 1,
                ],
            );
        }
    }
}
