<x-block>
    <div class="mt-12 grid grid-cols-2 gap-4">
        @foreach ($items as $item)
        <div class="border-b-[0.5px] border-gray-2 py-2 md:border-none ">
            <div class="text-base sm:text-lg lg:text-xl font-bold text-primary">{{ $item['title'] ?? '' }}</div>
            <div class="text-base mb-2 font-bold text-secondary font-poppins">{{ $item['work'] ?? ''}}</div>
        </div>
        @endforeach
    </div>
</x-block>
