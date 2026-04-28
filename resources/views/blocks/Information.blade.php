@php
$style = $style ?? 'container';
$css = match ($style) {
'container' => 'w-100 ',
'compact' => 'md:w-[90%] xl:w-[70%] 2xl:w-[65%] ml-0',
'border'=> ' border-l-4 border-solid border-gray-2 pl-2 md:pl-4 relative', // disable border
default => 'ml-0',
}

@endphp

<x-block class="">
    @if(isset($title) && $title)
    <x-common.line-title title="{{ $title }}" />
    @endif
    <div class="{{ isset($customBg) && $customBg ? $customBg : 'bg-white' }}">
        <div class="container text-wysiwyg  {{ isset($title) && $title ? 'pt-4' : 'pt-4' }}">
            <div class="{{ $css }} ">
                @if($style == 'border')
                {{-- <div class="absolute top-0 -left-3 w-6 h-6 rounded-full bg-primary"></div> --}}
                <div class="md:w-[90%] xl:w-[80%] 2xl:w-[80%] text-sm">

                    @endif

                    {!! $content !!}

                    @if($style == 'border')
                </div>
                @endif

                @if(isset($items) && $items)
                <div class="flex justify-evenly items-center mt-8">


                    @foreach ($items as $item)
                    @if(isset($item['route']) && $item['route'])
                    <x-link :attrs="$item['route']" class="px-6 py-6 text-md font-bold text-center text-white uppercase md:block
                        {{ $loop->index % 2 == 0 ? 'bg-secondary hover:opacity-90' : 'bg-primary hover:opacity-90' }}">
                    </x-link>
                    @endif
                    @endforeach
                    @endIf
                </div>
            </div>
        </div>
</x-block>
