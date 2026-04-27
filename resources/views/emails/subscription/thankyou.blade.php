<x-mail::message>
# ¡Gracias por suscribirte, {{ $subscriberName }}!

Te damos la bienvenida a nuestra comunidad.

Recibirás novedades y actualizaciones directamente en tu correo electrónico.

<x-mail::button :url="config('app.url')">
Visita Nuestro Sitio Web
</x-mail::button>

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>
