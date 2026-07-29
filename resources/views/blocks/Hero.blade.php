@php
    $title = $title ?? '';
    $subtitle = $subtitle ?? null;
    $buttonText = $buttonText ?? null;
    $buttonLink = $buttonLink ?? null;
    $hasButton = filled($buttonText) && filled($buttonLink);

    $titleLines = filled($title)
        ? preg_split("/\r\n|\r|\n/", $title)
        : [];

    $sp = 1;
@endphp

@if (filled($title))
    <x-block class="hero-block">
        <style>
.hero-block{min-height:100dvh}.hero-inner{--sp:var(--hero-speed,1);--t-s1:0s;--t-s2:calc(2s*var(--sp));--t-s3:calc((2s + .45s)*var(--sp));--t-s4:calc((2s + .45s + .3s)*var(--sp));--t-e:calc((2s + .45s + .3s + .9s)*var(--sp) + 1s);--b:clamp(2.75rem,8vw,5rem);--g:clamp(.15rem,.8vw,.35rem);--bt:80px;position:relative;min-height:100dvh;height:100dvh;background:#fff;color:#000;overflow:clip;z-index:0}
html.hero-scroll-lock,html.hero-scroll-lock body{overflow:hidden!important;touch-action:none;overscroll-behavior:none}
.hero-overlay{position:absolute;inset:0;width:100%;height:100%;background:#fff;z-index:1}
.hero-inner.is-playing .hero-overlay{position:fixed;z-index:9999;width:100dvw;height:100dvh}
.hero-line{position:absolute;left:50%;top:50%;width:0;height:1px;background:#000;transform:translate(-50%,-50%);opacity:0;z-index:1;animation:hl calc(.3s*var(--sp)) ease var(--t-s3) forwards}
@keyframes hl{0%{opacity:0;width:0}to{opacity:1;width:100dvw}}
.hero-logo{position:relative;z-index:2;display:flex;align-items:center;justify-content:center;gap:var(--g);animation:hd calc(.3s*var(--sp)) ease var(--t-s3) forwards}
@keyframes hd{0%{transform:translateY(0)}to{transform:translateY(calc(50dvh - var(--bt) - var(--b)/2))}}
.hero-letter{width:var(--b);height:var(--b);border-radius:9999px;display:grid;place-items:center;flex-shrink:0;overflow:hidden;background:var(--c);color:#fff}
.hero-letter svg{width:56%;height:56%;display:block}
.hero-letter svg text{fill:currentColor}
.hero-letter--s1{animation:hb calc(2s*var(--sp)) var(--t-s1) both,hi calc(.3s*var(--sp)) ease var(--t-s3) forwards}
.hero-letter:not(.hero-letter--s1){opacity:0;transform:scale(.35);width:0;animation:ha calc(.45s*var(--sp)) ease calc(var(--t-s2) + var(--st,.02s)) forwards,hi calc(.3s*var(--sp)) ease var(--t-s3) forwards}
@keyframes hb{0%{transform:translateY(calc(-50dvh - 120%));animation-timing-function:cubic-bezier(.55,.08,.68,.53)}42%{transform:translateY(0);animation-timing-function:cubic-bezier(.22,.61,.36,1)}58%{transform:translateY(-22%);animation-timing-function:cubic-bezier(.55,.08,.68,.53)}72%{transform:translateY(0);animation-timing-function:cubic-bezier(.22,.61,.36,1)}82%{transform:translateY(-10%);animation-timing-function:cubic-bezier(.55,.08,.68,.53)}90%{transform:translateY(0);animation-timing-function:cubic-bezier(.22,.61,.36,1)}95%{transform:translateY(-4%);animation-timing-function:cubic-bezier(.55,.08,.68,.53)}to{transform:translateY(0)}}
@keyframes ha{0%{opacity:0;transform:scale(.35);width:0}to{opacity:1;transform:scale(1);width:var(--b)}}
@keyframes hi{0%{background:var(--c);color:#fff}to{background:#fff;color:#000}}
.hero-content{position:absolute;inset:0;z-index:3;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1.25rem;text-align:center;pointer-events:none}
.hero-inner:not(.is-playing) .hero-content{pointer-events:auto}
.hero-title{margin:0;max-width:min(92vw,56rem);font-family:Poppins,sans-serif;font-weight:700;font-size:clamp(1.75rem,5.5vw,4rem);line-height:1.1;letter-spacing:-.02em;color:#000}
.hero-title__row{display:flex;flex-wrap:wrap;justify-content:center;align-items:center;gap:0}
.hero-title__char{display:inline-block;overflow:hidden;vertical-align:bottom;line-height:1.1}
.hero-title__char>span{display:inline-block;transform:translateY(110%);animation:hcu calc(.9s*var(--sp)) cubic-bezier(.16,1,.3,1) calc(var(--t-s4) + var(--d,0ms)) forwards}
@keyframes hcu{0%{transform:translateY(110%)}to{transform:translateY(0)}}
.hero-title__space{width:.28em}
.hero-title__mark{display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;opacity:0;transform:scale(.6)}
.hero-title__mark--arrow{width:.85em;height:.85em;color:#ff4d61;margin-right:.12em;animation:hpi .35s ease var(--t-e) forwards}
.hero-title__mark--dot{width:.42em;height:.42em;margin-left:.18em;border-radius:9999px;background:#ffc700;animation:hpi .35s ease calc(var(--t-e) + .07s) forwards}
@keyframes hpi{0%{opacity:0;transform:scale(.6)}to{opacity:1;transform:scale(1)}}
.hero-subtitle{margin-top:1.25rem;max-width:min(90vw,36rem);font-family:Manrope,sans-serif;font-size:clamp(.95rem,2vw,1.15rem);line-height:1.5;color:var(--color-gray,gray);opacity:0;transform:translateY(12px);animation:hfi .5s ease calc(var(--t-e) + .2s) forwards}
.hero-actions{margin-top:1.75rem;opacity:0;transform:translateY(12px);pointer-events:none;animation:hfi .5s ease calc(var(--t-e) + .35s) forwards}
.hero-inner:not(.is-playing) .hero-actions{pointer-events:auto}
@keyframes hfi{0%{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.hero-btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;min-height:2.75rem;padding:.7rem 1.4rem;border-radius:9999px;background:#000;color:#fff;font-family:Poppins,sans-serif;font-weight:600;font-size:.95rem;text-decoration:none;transition:background .2s,transform .2s}
.hero-btn:hover{background:var(--color-primary, #334155);transform:translateY(-1px)}
@media(prefers-reduced-motion:reduce){.hero-inner *{animation:none!important;transition:none!important}.hero-inner .hero-letter{opacity:1!important;transform:scale(1)!important;width:var(--b)!important;background:#fff!important;color:#000!important}.hero-inner .hero-logo{transform:translateY(calc(50dvh - var(--bt) - var(--b)/2))!important}.hero-inner .hero-line{opacity:1!important;width:100dvw!important}.hero-inner .hero-title__char>span,.hero-inner .hero-title__mark,.hero-inner .hero-subtitle,.hero-inner .hero-actions{opacity:1!important;transform:none!important}.hero-inner.is-playing .hero-overlay{position:absolute!important}}
        </style>
        <div
            x-data="{ playing: true, done: false }"
            x-init="
                if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    document.documentElement.classList.add('hero-scroll-lock');
                    const t = (2 + 0.45 + 0.3 + 0.9) * {{ $sp }} * 1000 + 1200;
                    setTimeout(() => { playing = false; done = true; document.documentElement.classList.remove('hero-scroll-lock'); }, t);
                } else {
                    playing = false; done = true;
                }
            "
            x-on:destroy="document.documentElement.classList.remove('hero-scroll-lock')"
            :class="{
                'is-playing': playing,
                'is-done': done,
            }"
            class="hero-inner"
            style="--hero-speed: {{ $sp }};"
            role="banner"
            aria-label="{{ $title }}"
        >
            <div class="hero-overlay">
                <div class="hero-stage" aria-hidden="true">
                    <div class="hero-line"></div>
                    <div class="hero-logo">
                        <div class="hero-letter hero-letter--s1" style="--c:#00f081">
                            <svg viewBox="0 0 100 100" aria-hidden="true"><text x="50" y="68" font-family="Poppins,sans-serif" font-weight="800" font-size="64" text-anchor="middle" fill="currentColor">S</text></svg>
                        </div>
                        <div class="hero-letter" style="--c:#1d71ff;--st:.04s">
                            <svg viewBox="0 0 100 100" aria-hidden="true"><text x="50" y="68" font-family="Poppins,sans-serif" font-weight="800" font-size="64" text-anchor="middle" fill="currentColor">O</text></svg>
                        </div>
                        <div class="hero-letter" style="--c:#ffc700;--st:.08s">
                            <svg viewBox="0 0 100 100" aria-hidden="true"><text x="50" y="68" font-family="Poppins,sans-serif" font-weight="800" font-size="64" text-anchor="middle" fill="currentColor">C</text></svg>
                        </div>
                        <div class="hero-letter" style="--c:#ff4d61;--st:.12s">
                            <svg viewBox="0 0 100 100" aria-hidden="true"><text x="50" y="68" font-family="Poppins,sans-serif" font-weight="800" font-size="64" text-anchor="middle" fill="currentColor">I</text></svg>
                        </div>
                        <div class="hero-letter" style="--c:#951b81;--st:.16s">
                            <svg viewBox="0 0 100 100" aria-hidden="true"><text x="50" y="68" font-family="Poppins,sans-serif" font-weight="800" font-size="64" text-anchor="middle" fill="currentColor">E</text></svg>
                        </div>
                        <div class="hero-letter" style="--c:#0dd6cc;--st:.2s">
                            <svg viewBox="0 0 100 100" aria-hidden="true"><text x="50" y="68" font-family="Poppins,sans-serif" font-weight="800" font-size="64" text-anchor="middle" fill="currentColor">S</text></svg>
                        </div>
                    </div>
                </div>
                <div class="hero-content">
                    <h1 class="hero-title">
                        @foreach ($titleLines as $li => $line)
                            <span class="hero-title__row">
                                @if ($li === 0)
                                    <span class="hero-title__mark hero-title__mark--arrow" aria-hidden="true">
                                        <x-lucide-move-down-right class="w-full h-full stroke-2" />
                                    </span>
                                @endif
                                @foreach (mb_str_split($line) as $ci => $ch)
                                    @if ($ch === ' ')
                                        <span class="hero-title__space" aria-hidden="true">&nbsp;</span>
                                    @else
                                        <span class="hero-title__char" style="--d:{{ ($li*12+$ci)*35 }}ms"><span>{{ $ch }}</span></span>
                                    @endif
                                @endforeach
                                @if ($loop->last)
                                    <span class="hero-title__mark hero-title__mark--dot" aria-hidden="true"></span>
                                @endif
                            </span>
                        @endforeach
                    </h1>
                    @if (filled($subtitle))
                        <p class="hero-subtitle">{{ $subtitle }}</p>
                    @endif
                    @if ($hasButton)
                        <div class="hero-actions">
                            <x-link :attrs="$buttonLink" class="hero-btn">{{ $buttonText }}</x-link>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </x-block>
@endif
