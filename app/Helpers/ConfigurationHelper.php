<?php

use App\Services\ConfigurationService;

if (! function_exists('app_config')) {
    /**
     * Get the configuration service instance
     */
    function app_config(): ConfigurationService
    {
        return app('app.config');
    }
}

if (! function_exists('config_value')) {
    /**
     * Get a configuration value by key
     *
     * @param  mixed  $default
     * @return mixed
     */
    function config_value(string $key, $default = null)
    {
        return app_config()->get($key, $default);
    }
}

if (! function_exists('config_text')) {
    /**
     * Get a text configuration value
     */
    function config_text(string $key, string $default = ''): string
    {
        return app_config()->text($key, $default);
    }
}

if (! function_exists('config_rich_text')) {
    /**
     * Get a rich text configuration value
     */
    function config_rich_text(string $key, string $default = ''): string
    {
        return app_config()->richText($key, $default);
    }
}

if (! function_exists('config_url')) {
    /**
     * Get a URL configuration value
     */
    function config_url(string $key, array $default = []): array
    {
        return app_config()->url($key, $default);
    }
}

if (! function_exists('config_url_string')) {
    /**
     * Get a URL configuration as string
     */
    function config_url_string(string $key, string $default = ''): string
    {
        return app_config()->urlString($key, $default);
    }
}

if (! function_exists('config_url_target')) {
    /**
     * Get the target attribute for a URL configuration
     */
    function config_url_target(string $key): string
    {
        return app_config()->urlTarget($key);
    }
}

if (! function_exists('config_image')) {
    /**
     * Get an image configuration value
     */
    function config_image(string $key, string $default = ''): string
    {
        return app_config()->image($key, $default);
    }
}

if (! function_exists('mb_split')) {
    /**
     * Compatibility polyfill for environments missing mb_split.
     *
     * @return array|false
     */
    function mb_split(string $pattern, string $string, int $limit = -1)
    {
        $delimiter = '/';
        $escaped = str_replace($delimiter, '\\'.$delimiter, $pattern);
        $regex = $delimiter.$escaped.$delimiter.'u';

        return preg_split($regex, $string, $limit);
    }
}

if (! function_exists('tiptap_converter')) {
    function tiptap_converter(): object
    {
        return new class
        {
            public function asHTML(mixed $content): string
            {
                if ($content === null || $content === '') {
                    return '';
                }

                if (is_string($content)) {
                    $decoded = json_decode($content, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return $content;
                    }

                    $content = $decoded;
                }

                if (! is_array($content)) {
                    return '';
                }

                return $this->renderNode($content);
            }

            private function renderChildren(array $node): string
            {
                return collect($node['content'] ?? [])
                    ->map(fn ($child): string => is_array($child) ? $this->renderNode($child) : '')
                    ->implode('');
            }

            private function renderNode(array $node): string
            {
                $type = $node['type'] ?? null;
                $text = htmlspecialchars((string) ($node['text'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $children = $this->renderChildren($node);

                return match ($type) {
                    'doc' => $children,
                    'text' => $this->renderText($text, $node['marks'] ?? []),
                    'paragraph' => '<p>'.$children.'</p>',
                    'heading' => '<h'.min(max((int) ($node['attrs']['level'] ?? 2), 1), 6).'>'.$children.'</h'.min(max((int) ($node['attrs']['level'] ?? 2), 1), 6).'>',
                    'bulletList' => '<ul>'.$children.'</ul>',
                    'orderedList' => '<ol>'.$children.'</ol>',
                    'listItem' => '<li>'.$children.'</li>',
                    'blockquote' => '<blockquote>'.$children.'</blockquote>',
                    'hardBreak' => '<br>',
                    'codeBlock' => '<pre><code>'.$children.'</code></pre>',
                    'image' => $this->renderImage($node['attrs'] ?? []),
                    default => $children,
                };
            }

            private function renderText(string $text, array $marks): string
            {
                foreach ($marks as $mark) {
                    $type = $mark['type'] ?? null;
                    $text = match ($type) {
                        'bold' => '<strong>'.$text.'</strong>',
                        'italic' => '<em>'.$text.'</em>',
                        'strike' => '<s>'.$text.'</s>',
                        'link' => '<a href="'.e((string) ($mark['attrs']['href'] ?? '#')).'">'.$text.'</a>',
                        default => $text,
                    };
                }

                return $text;
            }

            private function renderImage(array $attrs): string
            {
                $src = e((string) ($attrs['src'] ?? ''));
                $alt = e((string) ($attrs['alt'] ?? ''));

                return $src === '' ? '' : '<img src="'.$src.'" alt="'.$alt.'">';
            }
        };
    }
}
