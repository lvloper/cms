@php
    $htmlDescription = $description ?? '';
    if (is_array($htmlDescription)) {
        $renderTiptap = function ($node) use (&$renderTiptap): string {
            if (! is_array($node)) {
                return e((string) $node);
            }
            $type = $node['type'] ?? '';
            if ($type === 'text') {
                $txt = e($node['text'] ?? '');
                foreach ($node['marks'] ?? [] as $mark) {
                    $markType = is_array($mark) ? ($mark['type'] ?? '') : '';
                    $txt = match ($markType) {
                        'bold' => "<strong>{$txt}</strong>",
                        'italic' => "<em>{$txt}</em>",
                        'underline' => "<u>{$txt}</u>",
                        'strike' => "<s>{$txt}</s>",
                        'link' => '<a href="' . e(is_array($mark) ? ($mark['attrs']['href'] ?? '#') : '#') . '">' . $txt . '</a>',
                        default => $txt,
                    };
                }
                return $txt;
            }
            $children = '';
            foreach ($node['content'] ?? [] as $child) {
                $children .= $renderTiptap($child);
            }
            return match ($type) {
                'paragraph' => "<p>{$children}</p>",
                'heading' => '<h' . ($node['attrs']['level'] ?? 2) . ">{$children}</h" . ($node['attrs']['level'] ?? 2) . '>',
                'bulletList' => "<ul>{$children}</ul>",
                'orderedList' => "<ol>{$children}</ol>",
                'listItem' => "<li>{$children}</li>",
                'blockquote' => "<blockquote>{$children}</blockquote>",
                'codeBlock' => "<pre><code>{$children}</code></pre>",
                'horizontalRule' => '<hr>',
                'hardBreak' => '<br>',
                default => $children,
            };
        };
        $rendered = '';
        foreach ($htmlDescription as $node) {
            $rendered .= $renderTiptap($node);
        }
        $htmlDescription = $rendered;
    }
@endphp

<x-block class="py-12 md:py-16">
    <div class="container mx-auto px-4">
        @if ($title ?? null)
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 text-center">{{ $title }}</h2>
        @endif

        @if ($htmlDescription)
            <div class="prose prose-lg max-w-3xl mx-auto text-gray-600 text-center mb-10">
                {!! $htmlDescription !!}
            </div>
        @endif

        @if ($items ?? null)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($items as $item)
                    @php
                        $cardUrl = '';
                        $cardTarget = '';
                        $cardRoute = $item['route'] ?? [];
                        if (($cardRoute['route_id'] ?? null) === '0' && ($cardRoute['external_url'] ?? null)) {
                            $cardUrl = $cardRoute['external_url'];
                            $cardTarget = ($cardRoute['new_window'] ?? false) ? '_blank' : '';
                        } elseif ($cardRoute['route_id'] ?? null) {
                            $r = \App\Models\Route::find($cardRoute['route_id']);
                            if ($r) {
                                $cardUrl = url($r->full_slug);
                                $cardTarget = ($cardRoute['new_window'] ?? false) ? '_blank' : '';
                                if ($cardRoute['anchor'] ?? null) {
                                    $cardUrl .= '#' . $cardRoute['anchor'];
                                }
                            }
                        }
                    @endphp

                    <div class="group rounded-xl border border-gray-200 bg-white overflow-hidden hover:shadow-lg transition-shadow">
                        @if ($item['image'] ?? null)
                            <div class="aspect-[4/3] overflow-hidden bg-gray-100">
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::url($item['image']) }}"
                                    alt="{{ $item['title'] ?? '' }}"
                                    class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
                                >
                            </div>
                        @endif

                        <div class="p-5">
                            @if ($item['title'] ?? null)
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $item['title'] }}</h3>
                            @endif

                            @if ($item['description'] ?? null)
                                <p class="text-sm text-gray-600">{{ $item['description'] }}</p>
                            @endif

                            @if ($cardUrl)
                                <a
                                    href="{{ $cardUrl }}"
                                    @if ($cardTarget) target="{{ $cardTarget }}" rel="noopener noreferrer" @endif
                                    class="inline-flex items-center mt-3 text-sm font-medium text-primary hover:underline"
                                >
                                    {{ $cardRoute['btn_label'] ?? 'Ver más' }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-block>
