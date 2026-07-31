Nueva consulta de Socies

Persona: {{ $lead->name ?: 'Visitante sin nombre' }}
Estado: {{ $lead->status }}
Intención: {{ $lead->primary_intent_code ?: 'Sin clasificar' }}
Encaje: {{ $lead->fit_level ?: 'Sin definir' }}
Puntaje: {{ $lead->score ?? 'Pendiente' }}
Canal: {{ $lead->contact_channel ?: 'No informado' }}
Contacto: {{ $lead->email ?: ($lead->phone_e164 ?: 'No informado') }}
Campaña: {{ $lead->conversation?->campaign?->name ?: 'Directa' }}

Necesidad:
{{ $lead->problem_summary ?: 'Sin resumen disponible.' }}

Revisar en el panel: {{ url('/admin/paco-leads') }}
Conversación: {{ $lead->conversation_id }}
