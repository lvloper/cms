<x-block>
    <div class="relative py-12">
        <div class="absolute inset-0 bg-gray-2 md:h-[100px]"></div>
        <div class="container relative px-4 mx-auto max-w-[1180px]">
            <div class="flex flex-wrap gap-4 gap-y-10 justify-center lg:gap-6 px-3 md:px-0">
                @foreach ($items as $banner)
                <x-link :attrs="$banner['route']"
                    class="bg-primary block w-full md:w-[calc(33.333%-20px)] relative group cursor-pointer">

                    @if(isset($banner['icon']))
                    <x-icon :name="$banner['icon']"
                        class="block w-14 h-14 text-white absolute top-1/2 transform -translate-y-1/2 left-6 " />
                    @endif

                    <div
                        class="md:h-[119px] relative flex items-center {{ isset($banner['icon']) ? '' : 'justify-center' }}">
                        <div
                            class="flex absolute  inset-0 z-10 flex-col {{ isset($banner['icon']) ? 'items-start pl-[100px]' : 'items-center   md:pl-0' }} justify-center md:justify-center md:items-center md:static">
                            <span
                                class="block w-full font-bold {{ isset($banner['icon']) ? 'text-left' : 'text-center' }} text-white drop-shadow-lg text-md md:text-xl  md:drop-shadow-none">{{
                                $banner['title'] }}</span>
                            <span
                                class="block w-full font-bold {{ isset($banner['icon']) ? 'text-left' : 'text-center' }} text-white text-lg uppercase drop-shadow-lg md:text-xl  md:drop-shadow-none">{{
                                $banner['title2'] }}</span>
                        </div>
                        <div class="md:hidden h-[100px] overflow-hidden bg-primary">
                            {{--
                            <x-image :image="$banner['image']"
                                class="hidden md:block object-cover absolute inset-0 w-full h-full"
                                imageClass="w-full h-full object-cover absolute inset-0" /> --}}
                        </div>
                    </div>
                    {{-- <div class="md:h-[230px] overflow-hidden bg-primary">
                        @if(isset($banner['icon']))
                        <x-icon :name="$banner['icon']"
                            class="block md:hidden w-14 h-14 text-white absolute top-1/2 transform -translate-y-1/2 left-6" />
                        @endif
                        <x-image :image="$banner['image']"
                            class="hidden md:block object-cover w-[100px] md:w-full h-full"
                            imageClass="w-full h-full object-cover" :background="true" />
                    </div> --}}
                    <div
                        class="absolute -bottom-4 md:-bottom-4 w-full text-center md:opacity-0 transition-opacity group-hover:opacity-100">
                        @if($banner['route'])
                        <div
                            class="inline-block px-6 py-1 mt-6 md:mt-6 md:text-sm font-bold text-left md:text-center text-white bg-secondary">
                            {{ $banner['titleBtn'] ?? 'Conocé' }}
                        </div>
                        @endif
                    </div>
                </x-link>
                @endforeach
            </div>
        </div>
    </div>
</x-block>