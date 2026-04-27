@php
    $rawDescription = html_entity_decode((string) ($item['description'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $description = \Illuminate\Support\Str::of(strip_tags($rawDescription))->squish();
@endphp

<x-link :attrs="$item['route']" class="relative col-span-6 md:col-span-3 xl:col-span-2 block">
    <div
        x-data="{
            expanded: false,
            overflowing: false,
            checkOverflow() {
                const el = this.$refs.description;
                if (!el) return;

                const wasExpanded = this.expanded;
                this.expanded = false;

                this.$nextTick(() => {
                    this.overflowing = el.scrollHeight > (el.clientHeight + 1);
                    this.expanded = this.overflowing ? wasExpanded : false;
                });
            }
        }"
        x-init="$nextTick(() => checkOverflow())"
        @resize.window.debounce.200="checkOverflow()"
        class="relative group"
    >
        <div class="overflow-hidden bg-gray-3 block rounded-xl">
            <x-image :image="$item['image']" fit="cover" class="rounded-t-xl md:rounded-t-xl h-[180px] w-full" />

            <div class="px-4 py-4 md:py-6 flex min-h-[220px] flex-col">
                <div
                    class="text-lg leading-tight md:text-xl font-heavy"
                    style="height: 3.8rem; overflow: hidden; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2;"
                >
                    {{ $item['title'] }}
                </div>

                <div
                    x-ref="description"
                    class="mt-0.5 text-sm lg:block font-poppins text-gray-700 leading-6"
                    :style="expanded
                        ? 'overflow: visible; display: block; height: auto;'
                        : 'height: 9rem; overflow: hidden; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 6;'"
                >
                    {{ $description }}
                </div>

                <span
                    @click.prevent.stop="expanded = !expanded"
                    x-text="expanded ? 'Ver menos' : 'Seguir leyendo'"
                    :class="overflowing ? '' : 'invisible pointer-events-none'"
                    class="mt-2 min-h-[1rem] w-fit text-xs font-bold uppercase text-primary md:text-sm cursor-pointer"
                ></span>
            </div>
        </div>
    </div>
</x-link>
