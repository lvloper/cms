<x-block class="bg-gray-2">
    <div class="container">
        <div>
            <div class="grid grid-cols-1 gap-2 md:grid-cols-2 md:gap-6">
                @foreach ($items as $item)
                <div class="grid grid-cols-6 rounded-2xl gap-6 bg-gray-3 md:mx-0 max-h-[150px]">
                    <div class="col-span-2 h-[100px] md:h-[150px]">
                        <x-image :image="$item['image']" class="w-full h-full"
                            imageClass="rounded-ss-2xl rounded-es-2xl h-full w-full object-cover" />
                    </div>
                    <div class="flex flex-col justify-center col-span-4 pr-2">
                        <div class="text-base font-bold text-primary">{{ $item['title'] ?? ''}}</div>
                        <div class="text-sm leading md:w-[85%] font-poppins ">{{ $item['work'] ?? ''}}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @isset($route)
            <div class="w-full pt-4 pb-6 text-center">
                <x-link :attrs="$route"
                    class="mt-4 inline-block text-white px-4 py-0.75 text-base bg-secondary font-bold hover:bg-primary">
                </x-link>
            </div>
            @endisset
        </div>
    </div>
</x-block>