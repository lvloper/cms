<div class="">
    {{--
    <x-common.line-title size="3xl" title="Novedades" /> --}}

    <div class="py-6 mx-auto pt-12">
        <x-blog.tags-stories :tags="$this->highlightTags()" />
    </div>

    <div class="container mx-auto mb-6 max-w-screen-xl xl:mb-12">
        <div class="grid grid-cols-1 gap-6 items-start md:grid-cols-2 lg:grid-cols-3" x-data="{ 
            init() {
                Livewire.on('postsLoaded', () => {
                    this.$nextTick(() => {
                        this.$dispatch('reload:masonry')
                    })
                })
            }
        }" x-masonry>
            @foreach($this->posts->take(9) as $post)
            <div class="masonry-item">
                <x-blog.card :item="$post" />
            </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 items-start md:grid-cols-2 lg:grid-cols-3" x-data="{ 
            init() {
                Livewire.on('postsLoaded', () => {
                    this.$nextTick(() => {
                        this.$dispatch('reload:masonry')
                    })
                })
            }
        }" x-masonry>
            @if($this->posts->count() > 9)
            @foreach($this->posts->skip(9) as $post)
            <div class="masonry-item">
                <x-blog.card :item="$post" />
            </div>
            @endforeach
            @endif

            @if($this->posts->isEmpty())
            <p wire:loading.remove class="col-span-3 pt-12 mx-auto text-2xl text-center">No se encontraron publicaciones
            </p>
            @endif


            @for ($i = 0; $i < 7; $i++) <div @if($i==0 && !$noMorePosts && $loadCount < 4)
                x-intersect.full="$wire.loadMore();" wire:loading.remove @endif class="masonry-item">
                <div wire:loading class="block w-full loading-pulse" @if($i !=0) wire:target="loadMore" @endif>

                    <div class="overflow-hidden bg-white rounded-xl group">
                        <div class="md:w-[100%] rounded-lg md:rounded-3xl block ">
                            <div class="rounded-t-xl md:rounded-t-xl h-[180px] bg-image bg-gray-200 ">
                            </div>
                            <div class="px-4 py-4 space-y-3 md:py-6">
                                <div class="text-lg leading-tight md:text-xl font-heavy">
                                    <br>
                                    <br>
                                    <br>
                                </div>
                                <div class="pr-4 text-sm lg:block font-poppins slideText"> <br>
                                    <br>
                                    <br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        @endfor

        @if($loadCount >= 4 && !$noMorePosts)
        <div class="col-span-3 text-center">
            <button wire:click="loadMore"
                class="px-6 py-2 mx-auto font-bold text-center text-white uppercase text-md md:block bg-secondary border-secondary hover:bg-secondarylight">
                Ver más
            </button>
        </div>
        @endif
    </div>
</div>

@pushOnce('styles', 'masonry')

<script defer src="https://unpkg.com/alpinejs-masonry@latest/dist/masonry.min.js"></script>
<!-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> -->
@endPushOnce


</div>
