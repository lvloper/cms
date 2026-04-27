<x-block>
    <div>
        <div class="rounded-lg">
            <x-image :image="$image" imageClass='w-full py-8' />
        </div>
        <div class="leading-normal">
            <div class="text-2xl md:text-3xl font-heavy md:font-bold text-primary">{{ $title ?? '' }}</div>
            <div class="font-bold text-secondary text-md md:text-xl font-poppins">{{ $work ?? '' }}</div>
        </div>
        <div class="flex gap-4 text-white">
            @foreach ($items as $item)
                <x-link :attrs="$item['route']">
                    <div class="flex justify-center items-center my-4 w-7 h-7 rounded-full md:w-10 md:h-10 bg-primary">
                        <x-icon :name="$item['icon']" class="w-4 h-4 md:w-6 md:h-6" />
                    </div>
                </x-link>
            @endforeach
        </div>
        <div>
            <p class="normal-text">
            </p>
        </div>
    </div>
</x-block>
