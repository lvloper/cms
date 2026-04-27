@if($tags)
<swiper-container wire:ignore autoplay="true" class="container mx-auto" x-cloak x-data="{ 
    currentTag: '{{ $this->tag }}',
    setActive(slug) { 
        this.currentTag = slug;  
    }
    }" breakpoints='{
        "0": {
        "slidesPerView": 3,
        "spaceBetween": 10
        },
        "354": {
        "slidesPerView": 4,
        "spaceBetween": 10
        },
        "480": {
        "slidesPerView": 5,
        "spaceBetween": 10
        },
        "700": {
        "slidesPerView": "auto",
        "spaceBetween": 10
        },
        "768": {
        "slidesPerView": "auto", 
        "spaceBetween": 10
        },
        "1024": {
        "slidesPerView": "auto",
        "spaceBetween": 10
        },
        "1536": {
        "slidesPerView": "auto",
        "spaceBetween": 10
        }

    }'>
    @foreach ($tags->unique('slug') as $item)
    @if ($item && $item->thumb)
    <swiper-slide class="flex flex-col items-center max-w-[120px] cursor-pointer group pl-1">
    <div wire:click="$set('tag', currentTag === '{{ $item->slug }}' ? null : '{{ $item->slug }}')"
        @click="setActive(currentTag === '{{ $item->slug }}' ? null : '{{ $item->slug }}')">
        <div
        class="relative w-[60px] h-[60px] md:w-[100px] md:h-[100px] mx-auto rounded-full border-[3px] border-primary overflow-hidden">
        <div class="absolute inset-0 mix-blend-multiply transition-opacity duration-300 bg-primary group-hover:opacity-50"
            :class="{ 'opacity-50': currentTag === '{{ $item->slug }}', 'opacity-0': currentTag !== '{{ $item->slug }}' }">
        </div>
        <x-image :src="$item->thumb" class="object-cover w-full h-full" />
        </div>
        <div class="group-hover:text-primary font-sans text-sm font-medium text-center max-w-[100px] pt-2"
        :class="{ 'text-primary': currentTag === '{{ $item->slug }}', 'text-black': currentTag !== '{{ $item->slug }}' }">
        {{ Str::limit($item->name, 30, '...') }}
        </div>
    </div>
    </swiper-slide>
    @endif
    @endforeach
</swiper-container>
@endif