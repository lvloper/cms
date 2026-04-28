<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Rich Editor - Prueba de Pegado</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @filamentStyles
    @livewireStyles

    <style>
        .test-instructions {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
        .test-result {
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 0.85rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    {{ $slot }}

    @filamentScripts
    @livewireScripts
</body>
</html>
