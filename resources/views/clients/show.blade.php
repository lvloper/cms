<x-layout>
    <article class="bg-black text-white">
        <header class="container mx-auto px-4 py-12 md:py-16">
            <h1 class="sr-only">{{ $clientData['title'] }}</h1>

            @if ($clientData['logo'])
                <img
                    src="{{ $clientData['logo'] }}"
                    alt="Logo de {{ $clientData['title'] }}"
                    class="h-20 w-auto max-w-full object-contain md:h-28"
                >
            @endif
        </header>

        <x-blocks :blocks="$client->blocks ?? collect()" />

        @if (count($clientData['works']) > 0)
            <section class="container mx-auto px-4 py-12 md:py-16" aria-labelledby="client-works-title">
                <h2 id="client-works-title" class="mb-8 text-3xl font-bold md:text-4xl">Trabajos</h2>

                <ul class="grid grid-cols-1 gap-6 md:grid-cols-2" role="list">
                    @foreach ($clientData['works'] as $work)
                        <li class="border-t border-white/20 pt-6">
                            <a
                                href="{{ $work['external_url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="group block focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white"
                            >
                                @if ($work['image'])
                                    <img
                                        src="{{ $work['image'] }}"
                                        alt=""
                                        class="mb-5 aspect-[3/2] w-full object-cover"
                                        loading="lazy"
                                    >
                                @endif

                                <div class="flex items-start justify-between gap-4">
                                    <h3 class="text-xl font-bold md:text-2xl">{{ $work['title'] }}</h3>
                                    <span aria-hidden="true" class="text-xl transition-transform group-hover:translate-x-1">↗</span>
                                </div>

                                @if (! empty($work['description']))
                                    <p class="mt-3 max-w-2xl text-gray-2">{{ $work['description'] }}</p>
                                @endif

                                @if (! empty($work['categories']))
                                    <ul class="mt-4 flex flex-wrap gap-2" aria-label="Categorías">
                                        @foreach ($work['categories'] as $category)
                                            <li class="border border-white/20 px-3 py-1 text-xs uppercase tracking-wide">
                                                {{ \Illuminate\Support\Str::headline($category) }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if (count($clientData['testimonials']) > 0)
            <section class="container mx-auto px-4 py-12 md:py-16" aria-labelledby="client-testimonials-title">
                <h2 id="client-testimonials-title" class="mb-8 text-3xl font-bold md:text-4xl">Testimonios</h2>

                <ul class="grid grid-cols-1 gap-8 lg:grid-cols-2" role="list">
                    @foreach ($clientData['testimonials'] as $testimonial)
                        <li>
                            <figure class="border-l border-white/30 pl-6">
                                <blockquote class="text-xl leading-relaxed md:text-2xl">
                                    {!! $testimonial['testimonial'] !!}
                                </blockquote>
                                <figcaption class="mt-6 flex items-center gap-4">
                                    @if ($testimonial['image'])
                                        <img
                                            src="{{ $testimonial['image'] }}"
                                            alt=""
                                            class="h-14 w-14 rounded-full object-cover"
                                            loading="lazy"
                                        >
                                    @endif
                                    <span>
                                        <strong class="block">{{ $testimonial['person'] }}</strong>
                                        <span class="text-gray-2">{{ $testimonial['position'] }}</span>
                                    </span>
                                </figcaption>
                            </figure>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if (! empty($clientData['navigation']))
            <nav class="client-navigation container mx-auto px-4 py-8 md:py-12" aria-label="Navegación entre clientes">
                <div class="client-navigation__inner">
                    <a
                        href="{{ $clientData['navigation']['previous']['url'] }}"
                        class="client-navigation__link"
                        style="--client-reference-color: {{ $clientData['navigation']['previous']['color'] ?: 'var(--color-socies-green)' }}"
                    >
                        <span class="client-navigation__eyebrow">← Anterior</span>
                        <strong class="client-navigation__title">{{ $clientData['navigation']['previous']['title'] }}</strong>
                    </a>

                    <a
                        href="{{ $clientData['navigation']['next']['url'] }}"
                        class="client-navigation__link client-navigation__link--next"
                        style="--client-reference-color: {{ $clientData['navigation']['next']['color'] ?: 'var(--color-socies-green)' }}"
                    >
                        <span class="client-navigation__eyebrow">Siguiente →</span>
                        <strong class="client-navigation__title">{{ $clientData['navigation']['next']['title'] }}</strong>
                    </a>
                </div>
            </nav>
        @endif
    </article>
</x-layout>
