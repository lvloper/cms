<div>

    @php
        $buttonClass = match ($type ?? null) {
            'primary' => 'bg-primary text-white px-6 py-2 inline-block mb-2 hover:bg-primary-hover ',
            'secondary' => 'bg-secondary text-white px-6 py-2 inline-block mb-2 hover:bg-secondary-hover ',
            'big' => 'grid grid-cols-6 gap-0 items-center px-2 py-4 max-w-[450px] bg-gray-50 font-semibold text-left border-t-4 select-none md:px-8 group md:border-b-4 md:border-t-0 border-secondary',
            default => 'bg-primary text-white px-6 py-2 inline-block mb-2 ',
        };
    @endphp
    <x-link :attrs="$route" :class="$buttonClass">
        @if($type == 'big')
            <span
                class="col-span-5 leading-none text-xl uppercase font-bold group-hover:text-primary transition-all duration-400">{{ $text ?? '' }}</span>
            <div class="flex col-span-1 justify-end">
                <svg class="w-6 md:w-8 text-primary border-2 border-primary rounded-full duration-300 ease-out group-hover:-rotate-[45deg]"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.25"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
            </div>
        @else
            {{ $text }}
        @endif
    </x-link>
</div>