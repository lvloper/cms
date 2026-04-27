<a
    href="{{ $url ?? '' }}"
    class="{{ $class ?? '' }}
    
     grid grid-cols-6 gap-4 items-center px-4 py-6 w-full font-semibold text-left border-t-4 select-none md:px-8 group md:border-b-4 md:border-t-0 border-secondary">
    <span
        class="col-span-5 leading-none text-xl uppercase font-bold group-hover:text-primary transition-all duration-400 sm:w-[80%]">{{ $title ?? '' }}</span>
    <div class="flex col-span-1 justify-center">
        <svg class="w-8 md:w-10 text-primary border-2 border-primary rounded-full duration-300 ease-out group-hover:-rotate-[45deg]"
            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
        </svg>
    </div>
</a>
