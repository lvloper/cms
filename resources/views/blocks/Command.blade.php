<x-block class="relative bg-primary">
    <div class="container -mt-14 h-28">
        <div class="absolute top-0 z-50 pb-6 mx-auto w-[calc(100%-60px)] md:w-[500px]">
            @if(!isset($preview) || !$preview)
            <livewire:search-component direction="bottom" searchIn="{{ $searchIn['route_id'] ?? false }}" />
            @endif
        </div>
    </div>
</x-block>