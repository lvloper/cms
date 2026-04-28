<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('blog')
            ->select(['id', 'description', 'content'])
            ->orderBy('id')
            ->chunkById(100, function ($blogs): void {
                foreach ($blogs as $blog) {
                    DB::table('blog')
                        ->where('id', $blog->id)
                        ->update([
                            'description' => $this->toHtml($blog->description),
                            'content' => $this->toHtml($blog->content),
                        ]);
                }
            });

        DB::table('pages')
            ->select(['id', 'blocks'])
            ->orderBy('id')
            ->chunkById(100, function ($pages): void {
                foreach ($pages as $page) {
                    $blocks = json_decode($page->blocks, true);

                    if (json_last_error() !== JSON_ERROR_NONE || ! is_array($blocks)) {
                        continue;
                    }

                    DB::table('pages')
                        ->where('id', $page->id)
                        ->update(['blocks' => json_encode($this->convertNestedEditorDocs($blocks))]);
                }
            });

        DB::table('configurations')
            ->select(['id', 'value'])
            ->where('type', 'rich_text')
            ->orderBy('id')
            ->chunkById(100, function ($configurations): void {
                foreach ($configurations as $configuration) {
                    $value = json_decode($configuration->value, true);

                    if (json_last_error() !== JSON_ERROR_NONE || ! is_array($value)) {
                        continue;
                    }

                    $value['rich_content'] = $this->toHtml($value['rich_content'] ?? '');

                    DB::table('configurations')
                        ->where('id', $configuration->id)
                        ->update(['value' => json_encode($value)]);
                }
            });
    }

    public function down(): void
    {
        // HTML cannot be reliably converted back to the old editor JSON structure.
    }

    private function convertNestedEditorDocs(mixed $value): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && $this->isEditorDoc($decoded)) {
                return $this->toHtml($decoded);
            }

            return $value;
        }

        if (! is_array($value)) {
            return $value;
        }

        if ($this->isEditorDoc($value)) {
            return $this->toHtml($value);
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->convertNestedEditorDocs($item);
        }

        return $value;
    }

    private function toHtml(mixed $content): string
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

        if (! $this->isEditorDoc($content)) {
            return is_scalar($content) ? (string) $content : '';
        }

        return $this->renderNode($content);
    }

    private function isEditorDoc(mixed $content): bool
    {
        return is_array($content)
            && ($content['type'] ?? null) === 'doc'
            && isset($content['content'])
            && is_array($content['content']);
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
        $children = $this->renderChildren($node);

        return match ($type) {
            'doc' => $children,
            'text' => $this->renderText($node),
            'paragraph' => '<p>'.$children.'</p>',
            'heading' => $this->renderHeading($node, $children),
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

    private function renderText(array $node): string
    {
        $text = htmlspecialchars((string) ($node['text'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        foreach ($node['marks'] ?? [] as $mark) {
            $text = match ($mark['type'] ?? null) {
                'bold' => '<strong>'.$text.'</strong>',
                'italic' => '<em>'.$text.'</em>',
                'strike' => '<s>'.$text.'</s>',
                'link' => '<a href="'.htmlspecialchars((string) ($mark['attrs']['href'] ?? '#'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'">'.$text.'</a>',
                default => $text,
            };
        }

        return $text;
    }

    private function renderHeading(array $node, string $children): string
    {
        $level = min(max((int) ($node['attrs']['level'] ?? 2), 1), 6);

        return '<h'.$level.'>'.$children.'</h'.$level.'>';
    }

    private function renderImage(array $attrs): string
    {
        $src = htmlspecialchars((string) ($attrs['src'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $alt = htmlspecialchars((string) ($attrs['alt'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return $src === '' ? '' : '<img src="'.$src.'" alt="'.$alt.'">';
    }
};
