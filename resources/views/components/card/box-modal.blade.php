<x-modal ref="{{ $id }}">
    <x-slot name="button">
        <div
            class="flex gap-2 items-center px-1 py-2 max-w-[450px] justify-between bg-gray-50 font-semibold text-left border-t-4 select-none md:px-8 group md:border-b-4 md:border-t-0 border-secondary">
            <span class="w-full leading-none text-base font-bold
        leading-tight
        group-hover:text-primary transition-all duration-400 sm:w-[80%]">{{ $item['title'] ?? '' }}</span>
            <div class="flex justify-end col-span-1">
                <svg class="w-6 md:w-8 text-primary border-2 border-primary rounded-full duration-300 ease-out group-hover:-rotate-[45deg]"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.25"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
            </div>
    </x-slot>
    <x-slot name="content">
        <div class="text-wysiwyg">
            <div class="mb-2 text-xl font-bold leading-tight sm:text-xl 4xl:text-xl text-primary">
                {{ $item['title'] ?? '' }}
            </div>
            <p>
                {!! $item['description'] ?? '' !!}
            </p>

            <x-link :attrs=" $item['route']" :hideIfNull="true">
                <button class="px-6 py-2 mx-8 mb-8 text-white rounded-full text-md bg-secondary">
                    Más info
                </button>
            </x-link>

            @if (is_array($item['images']) && count($item['images']) > 0 || $item['images'])
            <div class="overflow-hidden ">
                <swiper-container class="mySwiper max-h-[20rem] w-full" navigation="true">
                    @if (is_array($item['images']))
                    @foreach ($item['images'] as $image)
                    <swiper-slide>
                        <img src="/storage/{{ $image }}" class="accordeon-img w-full max-w-full h-auto object-cover block">
                    </swiper-slide>
                    @endforeach
                    @else
                    <swiper-slide>
                        <img src="/storage/{{ $item['images'] }}" class="accordeon-img w-full max-w-full h-auto object-cover block">
                    </swiper-slide>
                    @endif
                </swiper-container>
            </div>
            @endif
        </div>
    </x-slot>
</x-modal>
