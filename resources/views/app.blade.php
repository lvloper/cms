<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
    <link rel="icon" href="{{ asset('favicon-16x16.png') }}" type="image/png" sizes="16x16">
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}" type="image/png" sizes="32x32">
    <link rel="icon" href="{{ asset('favicon-96x96.png') }}" type="image/png" sizes="96x96">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" sizes="180x180">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @php
        $hotPath = public_path('hot');
        $viteUrl = file_exists($hotPath) ? trim(file_get_contents($hotPath)) : null;
    @endphp
    @if($viteUrl)
        <script type="module">
            import RefreshRuntime from '{{ $viteUrl }}/@react-refresh'
            RefreshRuntime.injectIntoGlobalHook(window)
            window.$RefreshReg$ = () => {}
            window.$RefreshSig$ = () => (type) => type
            window.__vite_plugin_react_preamble_installed__ = true
        </script>
    @endif
    @inertiaHead

    @php
        $headScriptsConfig = \App\Models\Configuration::getValue('head_scripts');
        $headScripts = is_array($headScriptsConfig) && isset($headScriptsConfig['text']) ? $headScriptsConfig['text'] : null;
    @endphp
    @if($headScripts)
    {!! $headScripts !!}
    @endif
</head>
<body>
    @inertia

    {{-- Global body scripts from configuration --}}
    @php
        $bodyScriptsConfig = \App\Models\Configuration::getValue('body_scripts');
        $bodyScripts = is_array($bodyScriptsConfig) && isset($bodyScriptsConfig['text']) ? $bodyScriptsConfig['text'] : null;
    @endphp
    @if($bodyScripts)
    {!! $bodyScripts !!}
    @endif
</body>
</html>
