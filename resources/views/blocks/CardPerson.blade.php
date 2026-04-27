<x-block>
    <div class="grid grid-cols-6 rounded-3xl bg-gray-3 md:mx-0 max-h-[150px]">
        <div class="col-span-2 h-[150px]">
            <x-image :image="$image" class="h-full w-full"
                imageClass=" rounded-ss-3xl rounded-es-3xl h-full w-full object-cover" />
        </div>
        <div class="flex flex-col col-span-4 justify-center px-4 py-6 md:px-4">
            <div class="text-lg font-heavy md:font-bold text-primary">{{ $title ?? ''}}</div>
            <div class="text-base mb-2 font-bold text-secondary font-poppins">{{ $work ?? ''}}
            </div>
        </div>
    </div>
</x-block>
