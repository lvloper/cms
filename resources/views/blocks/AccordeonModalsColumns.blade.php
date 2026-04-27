<x-block class="bg-gray-3">

    <x-common.line-title  title="{{ $title }}" />
    <div class="py-6">
        <div class="container">
            <div
                class="mx-auto md:mx-0 pb-0 description-normal lg:w-[70%]">
                {!! $description ?? '' !!}
            </div>
        </div>
        <div class="container mb-4">
            <div class="grid md:grid-cols-2 h-full grid-cols-1 gap-y-0 gap-x-4 md:gap-y-4 mx-auto">
                @foreach ($items as $item)
                    <x-card.box-modal :item="$item" :id="$id . '-' . $loop->iteration" />
                @endforeach
            </div>
        </div>

    </div>
    <style>
        .item-animate{
            opacity: 0
        }
    </style>
   <script>
    document.addEventListener('livewire:navigated', function() {
        const BLOCK = document.getElementById('{{ $id }}');
        const items = document.querySelectorAll('.item-animate');

        items.forEach((item, index) => {
            gsap.to(item, {
                scrollTrigger: {
                    trigger: BLOCK,
                    start: "top 50%", 
                    end: "bottom 20%",
                     toggleActions: 'play none play none'
                },
                y: 0,
                opacity: 1,
                duration: 0.8,
                delay: index * 0.2, 
                ease: "power2.out"
            });
        });
    });
</script>
</x-block>
