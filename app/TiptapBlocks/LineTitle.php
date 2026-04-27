<?php

namespace App\TiptapBlocks;

use FilamentTiptapEditor\TiptapBlock;

class LineTitle extends TiptapBlock
{
    public string $preview = 'tiptap-blocks.previews.line-title';

    public string $rendered = 'tiptap-blocks.rendered.line-title';

    public function getFormSchema(): array
    {
        return [
            //
        ];
    }
}