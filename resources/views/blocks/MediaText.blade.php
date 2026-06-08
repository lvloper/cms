@php
    $layout = $layout ?? 'left';
    $mediaType = $media_type ?? 'image';
    $isLeft = $layout === 'left';

    $htmlContent = $content ?? '';
    if (is_array($htmlContent)) {
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
            <div class="{{ $isLeft ? 'md:order-1' : 'md:order-2' }}">
                @if ($mediaType === 'youtube' && ($youtube_id ?? null))
                    <div class="relative aspect-video overflow-hidden rounded-lg bg-gray-100">
                        <iframe
                            src="https://www.youtube-nocookie.com/embed/{{ $youtube_id }}"
                            title="YouTube video"
                            class="absolute inset-0 h-full w-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    </div>
                @elseif ($mediaType === 'upload' && ($video_file ?? null))
                    <div class="relative aspect-video overflow-hidden rounded-lg bg-gray-100">
                        <video controls class="h-full w-full" preload="metadata">
                            <source src="{{ \Illuminate\Support\Facades\Storage::url($video_file) }}" type="video/mp4">
                        </video>
                    </div>
                @elseif ($mediaType === 'image' && ($image ?? null))
                    <div class="overflow-hidden rounded-lg bg-gray-100">
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::url($image) }}"
                            alt="{{ $title ?? '' }}"
                            class="h-full w-full object-cover"
                        >
                    </div>
                @endif
            </div>

            <div class="{{ $isLeft ? 'md:order-2' : 'md:order-1' }}">
                @if ($title ?? null)
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $title }}</h2>
                @endif

                @if ($htmlContent)
                    <div class="prose prose-lg max-w-none text-gray-700 [&_p]:mb-4 [&_p:last-child]:mb-0 [&_a]:text-primary [&_a]:underline">
                        {!! $htmlContent !!}
                    </div>
                @endif

                @if ($cta ?? null)
                    @php
                        $ctaUrl = '';
                        $ctaTarget = '';
                        if (($cta['route_id'] ?? null) === '0' && ($cta['external_url'] ?? null)) {
                            $ctaUrl = $cta['external_url'];
                            $ctaTarget = ($cta['new_window'] ?? false) ? '_blank' : '';
                        } elseif ($cta['route_id'] ?? null) {
                            $ctaRoute = \App\Models\Route::find($cta['route_id']);
                            if ($ctaRoute) {
                                $ctaUrl = url($ctaRoute->full_slug);
                                $ctaTarget = ($cta['new_window'] ?? false) ? '_blank' : '';
                                if ($cta['anchor'] ?? null) {
                                    $ctaUrl .= '#' . $cta['anchor'];
                                }
                            }
                        }
                    @endphp
                    @if ($ctaUrl)
                        <a
                            href="{{ $ctaUrl }}"
                            @if ($ctaTarget) target="{{ $ctaTarget }}" rel="noopener noreferrer" @endif
                            class="inline-flex items-center mt-6 px-6 py-3 rounded-lg bg-primary text-white font-semibold hover:bg-primary-hover transition-colors"
                        >
                            {{ $cta['btn_label'] ?? 'Ver más' }}
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-block>
