@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación de materiales" class="mt-6">
        <ul class="inline-flex items-center gap-1 text-sm">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li aria-disabled="true" aria-label="Anterior">
                    <span class="px-3 py-2 rounded bg-gray-200 text-gray-400 cursor-not-allowed">«</span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior"
                       class="px-3 py-2 rounded bg-white border border-gray-300 hover:bg-primary hover:text-white transition">«</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- Ellipsis --}}
                @if (is_string($element))
                    <li aria-disabled="true"><span class="px-3 py-2 text-gray-500">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li aria-current="page">
                                <span class="px-3 py-2 rounded bg-primary text-white font-semibold">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}" class="px-3 py-2 rounded bg-white border border-gray-300 hover:bg-primary hover:text-white transition"
                                   aria-label="Ir a la página {{ $page }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Siguiente"
                       class="px-3 py-2 rounded bg-white border border-gray-300 hover:bg-primary hover:text-white transition">»</a>
                </li>
            @else
                <li aria-disabled="true" aria-label="Siguiente">
                    <span class="px-3 py-2 rounded bg-gray-200 text-gray-400 cursor-not-allowed">»</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
