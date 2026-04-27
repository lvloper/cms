<x-block>
    <x-common.line-title title="{{ $title }}" size="xl" class="mb-0" />
    <div class="bg-gray-3">

        <div class="font-poppins  py-6">
            <div class="container gap-8 grid grid-cols-1 {{$image ? 'xl:grid-cols-3' : 'xl:grid-cols-1'}}  ">
                <div class="col-span-1 ">
                    <x-image :image="$image" class="sticky top-40" />
                </div>
                <div class="col-span-2">
                    @if($description)
                    <div class="w-full text-wysiwyg normal-text xl:w-[80%] grid grid-cols-1 gap-4 sm:gap-6 md:gap-8">
                        {!! $description ?? '' !!}
                    </div>
                    @endIf

                    <div class=" mt-6 sm:mt-8 2xl:w-[80%]">
                        @foreach ($items as $index => $item)
                        <div
                            class="pb-4 pl-8 relative {{ $index === count($items) - 1 ? '' : 'border-l-2 border-l-primary' }}">
                            <div class="w-6 h-6 bg-primary rounded-full absolute -left-3 top-0"></div>
                            <p class="normal-text">
                                <span class="font-bold text-primary">
                                    {{ $item['title'] }}
                                </span>
                                {!! $item['description'] !!}
                            </p>
                        </div>
                        @endforeach
                    </div>


                    @if(isset($description2) && $description2)
                    <div
                        class="w-full text-wysiwyg normal-text xl:w-[80%] grid grid-cols-1 gap-4 sm:gap-6 md:gap-8 mt-6">
                        {!! $description2 ?? '' !!}
                    </div>
                    @endIf
                </div>
            </div>
        </div>
    </div>
</x-block>

{{-- <script>
    document.addEventListener('livewire:navigated', function() {
        const BLOCK = document.getElementById('{{ $id }}');
        const masonryItems = BLOCK.querySelectorAll('.masonry-item');


        masonryItems.forEach((item, index) => {
            gsap.fromTo(item, {
                y: 50,
                opacity: 0
            }, {
                scrollTrigger: {
                    trigger: item,
                    start: "top 80%",
                },
                y: 0,
                opacity: 1,
                duration: 1,
                delay: index * 0.5,
                ease: "power2.out"
            });
        });
    });
</script> --}}