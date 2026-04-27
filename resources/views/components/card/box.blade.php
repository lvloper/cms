<x-link :attrs="$item['route'] ?? null" class="{{ $class ?? '' }} block grid grid-cols-6 gap-4 h-full
 items-center w-full px-4 md:px-8 font-semibold group text-left border-t-4 md:border-b-4
  md:border-t-0 select-none py-6 bg-white border-secondary">
    <span class="col-span-5 leading-none text-base font-bold 
        leading-tight
        group-hover:text-primary transition-all duration-400 sm:w-[80%]">{{ $item['title'] ?? '' }}</span>
    <div class="flex col-span-1 justify-center">
        <svg class="w-6 md:w-8 text-primary border-2 border-primary rounded-full duration-300 ease-out group-hover:-rotate-[45deg]"
            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.25"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
        </svg>
    </div>
</x-link>