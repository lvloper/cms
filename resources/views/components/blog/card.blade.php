<div class="overflow-hidden bg-white rounded-xl group">
    <a href="{{ $item->url }}" class="md:w-[100%] rounded-lg md:rounded-3xl block ">

        <x-image :src="$item->thumb" fit="cover" class="rounded-t-xl md:rounded-t-xl aspect-[1.91/1] bg-gray-200"
            default="{{ asset('img/layout/default.webp') }}" />
        <div class="p-3 space-y-3 md:px-4 md:py-6">
            <div class="text-md leading-tight md:text-md 
            font-heavy h-[110px] line-clamp-4 overflow-hidden">
                {{ Str::limit($item->title ?? '', 200) }}
            </div>
            {{-- <div class="hidden pr-4 text-xs lg:block font-poppins slideText">
                {!! Str::limit($item->short_description, 350, '...') !!}
            </div> --}}
            <div class="flex items-center space-x-1 text-xs font-bold uppercase text-primary md:text-sm">

                <span>Seguir Leyendo</span>

            </div>
        </div>
    </a>
</div>