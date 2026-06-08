@php
    $widthClass = match ($width ?? 'container') {
        'narrow' => 'max-w-3xl',
        'wide' => 'max-w-7xl',
        default => 'max-w-5xl',
    };

    $htmlContent = $content ?? '';
    if (is_array($htmlContent)) {
        $renderTiptap = null;
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
        foreach ($htmlContent as $node) {
            $rendered .= $renderTiptap($node);
        }
        $htmlContent = $rendered;
    }
@endphp

<x-block class="py-12 md:py-16">
    <div class="container mx-auto px-4">
        <div class="mx-auto {{ $widthClass }}">
            @if ($eyebrow ?? null)
                <p class="text-sm font-semibold tracking-widest uppercase text-gray-500 mb-3">{{ $eyebrow }}</p>
            @endif

            @if ($title ?? null)
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">{{ $title }}</h2>
            @endif

            @if ($htmlContent)
                <div class="prose prose-lg max-w-none text-gray-700 [&_p]:mb-4 [&_p:last-child]:mb-0 [&_a]:text-primary [&_a]:underline [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_li]:mb-1">
                    {!! $htmlContent !!}
                </div>
            @endif
        </div>
    </div>
</x-block>
