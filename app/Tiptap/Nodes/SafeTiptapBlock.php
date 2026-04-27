<?php

namespace App\Tiptap\Nodes;

use FilamentTiptapEditor\Extensions\Nodes\TiptapBlock as BaseTiptapBlock;
use Tiptap\Utils\HTML;

/**
 * Override del nodo tiptapBlock para normalizar la data sin tocar vendor.
 */
class SafeTiptapBlock extends BaseTiptapBlock
{
    public function renderHTML($node, $HTMLAttributes = []): array
    {
        $blocks = $this->getBlocks();

        $view = '';
        $block = $blocks[$node->attrs->type] ?? null;
        if ($block) {
            $data = $this->normalizeData($node->attrs->data ?? null);
            $view = $block->getRendered($data);
        }

        return [
            'tiptap-block',
            HTML::mergeAttributes($HTMLAttributes),
            'content' => $view,
        ];
    }

    protected function normalizeData(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (is_object($raw)) {
            return json_decode(json_encode($raw), true) ?? [];
        }
        if (is_string($raw)) {
            $processed = $raw;

            // Detecta JSON.parse('...') o JSON.parse("...") y extrae el interior.
            if (preg_match("~^JSON\\.parse\('(.*)'\)$~", $processed, $m)) {
                $processed = $m[1];
            } elseif (preg_match('~^JSON\\.parse\("(.*)"\)$~', $processed, $m)) {
                $processed = $m[1];
            }

            // Reemplaza u0022 / \u0022 por comillas
            $processed = str_replace(['\\u0022', 'u0022'], '"', $processed);

            $decoded = json_decode($processed, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            $second = json_decode(stripslashes($processed), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($second)) {
                return $second;
            }
            return ['value' => $raw];
        }
        return [];
    }
}
