@php($projects = $projects ?? [])

<x-block class="client-projects overflow-hidden border-b border-white/20" data-client-projects>
    <div class="client-projects__pin py-12 md:py-16" data-client-projects-pin>
        <div class="container mx-auto">
            @if($eyebrow ?? false)<p class="mb-4 text-xs font-bold tracking-widest text-socies-green">{{ $eyebrow }}</p>@endif
            @if($title ?? false)<h2 class="max-w-4xl text-3xl font-bold md:text-5xl">{{ $title }}</h2>@endif
            @if($intro ?? false)<p class="mt-5 max-w-2xl text-gray-2">{{ $intro }}</p>@endif
            <div class="client-projects__viewport mt-10 md:mt-14">
                <ol class="client-projects__track" data-client-projects-track>
                    @foreach($projects as $index => $project)
                        <li class="client-projects__item">
                            <article class="grid border-t border-white/30 pt-5 md:grid-cols-12 md:gap-8">
                                <div class="md:col-span-8">
                                    <x-client.case-media
                                        :type="$project['media_type'] ?? 'image'"
                                        :image="$project['media_image'] ?? null"
                                        :video="$project['media_video'] ?? null"
                                        :alt="$project['media_alt'] ?? ''"
                                        :placeholder="$project['media_placeholder'] ?? null"
                                        :autoplay="$project['media_autoplay'] ?? false"
                                        class="aspect-video"
                                    />
                                </div>
                                <div class="mt-6 md:col-span-4 md:mt-0">
                                    <p class="text-xs font-bold tracking-widest text-socies-coral">{{ $project['eyebrow'] ?? str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</p>
                                    <h3 class="mt-4 text-2xl font-bold">{{ $project['title'] ?? '' }}</h3>
                                    @if($project['summary'] ?? false)<p class="mt-5 text-gray-2">{{ $project['summary'] }}</p>@endif
                                </div>
                            </article>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</x-block>
