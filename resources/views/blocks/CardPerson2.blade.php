<x-block>
    <div class="grid relative gap-2 mb-8 lg:gap-8 lg:grid-cols-2">
        <div>
            <x-image :image="$image" imageClass='w-fit mx-auto rounded-xl overflow-hidden max-h-[220px] object-contain' />
        </div>
        <div class="flex flex-col justify-center">
            <div class="leading-normal">
                <div class="text-lg font-heavy md:font-bold text-primary">{{ $title ?? '' }}</div>
                <div class="text-base mb-2 font-bold text-secondary font-poppins">{{ $work ?? '' }}</div>
            </div>
            <div class="leading-normal text-sm">
                {!! $text !!}
            </div>
            <div class="flex gap-4 text-white">
                @foreach ($items as $item)
                <x-link :attrs="$item['route']">
                    <div class="flex justify-center items-center my-4 w-6 h-6 rounded-full bg-primary">
                        @if($item['icon'])
                        <x-icon :name="$item['icon']" class="w-3 h-3 md:w-3 md:h-3" />
                        @endif
                    </div>
                </x-link>
                @endforeach
            </div>
        </div>
        <x-link :attrs="$button_route" :hideIfNull="true" class="absolute right-0 bottom-0 group">
            <div class="flex justify-center pb-4 font-bold text-primary">
                Ver más
                {{--
                <x-lucide-arrow-right class="ml-4 w-3 h-3 transition-transform stroke-2 group-hover:translate-x-1" />
                --}}
            </div>
        </x-link>
    </div>
</x-block>
