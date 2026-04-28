<x-block>
    <div class="pt-4">
        @if ($title)
            <h1 class="mb-6 text-xl font-bold leading-tight sm:text-2xl lg:text-3xl text-primary md:uppercase">{{ $title }}</h1>
        @endif
        @if ($text)
            <div class="px-0 text-wysiwyg">
                {!! $text !!}
            </div>
        @endif
    </div>
</x-block>
