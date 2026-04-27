<x-block>
    <div class="bg-gray-3">
        <div class="container  py-12">
            <div class="mb-8 md:w-[90%] xl:w-[70%] 2xl:w-[65%] text-base">
                <h2 class="title-normal text-primary uppercase">{{ $title ?? '' }}</h2>
                <div class="text-wysiwyg">{!! $description ?? '' !!}</div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-[1000px]">
                @foreach ($items as $item)
                <div class="border-b-[0.5px] border-gray-2 py-2 md:border-none  ">

                    <x-image class="h-[50px] w-[50px] mb-4" :image="$item['icon']" />
                    <div class="border-b-4 border-primary mb-4 w-[70px] "></div>
                    <div class="text-base  font-bold text-primary text-balance mb-2">{{ $item['title'] ?? '' }}</div>
                    <div class="font-poppins  text-base sm:text-base lg:text-base leading-tight text-balance ">{!!
                        $item['description'] ?? '' !!}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-block>
