<script>
    function runFadeIn() {
        try {
            const fadeIn = document.querySelectorAll('.fade-in');
            if (!fadeIn || !fadeIn.length || typeof gsap === 'undefined') return;
            fadeIn.forEach((el) => {
                if (!el) return;
                gsap.to(el, {
                    opacity: 1,
                    duration: 0.5,
                    ease: "power2.inOut"
                });
            });
        } catch (e) {
            // swallow animation errors to avoid breaking UX
        }
    }

    document.addEventListener('DOMContentLoaded', runFadeIn);
    document.addEventListener('livewire:navigated', runFadeIn);
</script>

@stack('modals')
@livewireScriptConfig
@stack('scripts')


@if (env('APP_ENV') === 'production')
<script src="https://unpkg.com/lucide@latest"></script>
@else
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
@endif

<script type="module" src="https://cdn.jsdelivr.net/npm/@justinribeiro/lite-youtube@1.5.0/lite-youtube.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollToPlugin.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/TextPlugin.min.js"></script>

<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-element-bundle.min.js"></script>

<script>
    gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);
</script>

{{-- Footer scripts from route configuration --}}
@isset($route)
    @if($route->footer_scripts)
        {!! $route->footer_scripts !!}
    @endif
@endisset

{{-- Global body scripts from configuration --}}
@php
    $bodyScriptsConfig = \App\Models\Configuration::getValue('body_scripts');
    $bodyScripts = is_array($bodyScriptsConfig) && isset($bodyScriptsConfig['text']) ? $bodyScriptsConfig['text'] : null;
@endphp
@if($bodyScripts)
{!! $bodyScripts !!}
@endif