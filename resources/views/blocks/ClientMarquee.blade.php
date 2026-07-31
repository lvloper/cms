@php($items = $items ?? [])

<x-block class="overflow-hidden border-b border-white/20 py-8 md:py-12">
    @if(count($items))
        <div class="client-marquee client-marquee--{{ $speed ?? 'slow' }} client-marquee--{{ $direction ?? 'left' }}">
            <div class="client-marquee__track gap-6 text-3xl font-bold md:gap-10 md:text-5xl">
                @foreach([0, 1] as $copy)
                    <div class="flex shrink-0 gap-6 pr-6 md:gap-10 md:pr-10" @if($copy === 1) aria-hidden="true" @endif>
                        @foreach($items as $item)
                            <span class="flex shrink-0 items-center gap-6 md:gap-10">
                                <span>{{ $item }}</span>
                                <span class="h-3 w-3 rounded-full bg-socies-yellow md:h-4 md:w-4" aria-hidden="true"></span>
                            </span>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-block>
