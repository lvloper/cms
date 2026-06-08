<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="frontend">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css'])

    @stack('styles')
    @filamentStyles
    @livewireStyles
</head>
<body class="overflow-hidden font-sans text-base tracking-normal leading-normal text-gray-800">
    <div id="main">
        {{ $slot }}
    </div>
</body>
</html>
