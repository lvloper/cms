<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta property="og:locale" content="es_AR">

    @isset($route)
    {!! seo($route) !!}
    @endisset

    @stack('head')

    @livewireStyles

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
    <link rel="icon" href="{{ asset('favicon-16x16.png') }}" type="image/png" sizes="16x16">
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}" type="image/png" sizes="32x32">
    <link rel="icon" href="{{ asset('favicon-96x96.png') }}" type="image/png" sizes="96x96">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" sizes="180x180">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#ffffff">


    {{--
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet"> --}}
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])


    @stack('styles')
    @filamentStyles


    {{-- Custom CSS from route configuration --}}
    @isset($route)
    @if($route->custom_css)
    <style>
        {
             ! ! $route->custom_css  ! !
        }
    </style>
    @endif

    {{-- Header scripts from route configuration --}}
    @if($route->header_scripts)
    {!! $route->header_scripts !!}
    @endif
    @endisset

    {{-- Global head scripts from configuration --}}
    @php
        $headScriptsConfig = \App\Models\Configuration::getValue('head_scripts');
        $headScripts = is_array($headScriptsConfig) && isset($headScriptsConfig['text']) ? $headScriptsConfig['text'] : null;
    @endphp
    @if($headScripts)
    {!! $headScripts !!}
    @endif
</head>
