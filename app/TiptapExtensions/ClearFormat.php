<?php

namespace App\TiptapExtensions;

use Tiptap\Core\Extension;

class ClearFormat extends Extension
{
    public static $name = 'clearFormat';

    public function addGlobalAttributes(): array
    {
        return [];
    }

    public function parseHTML(): array
    {
        return [];
    }

    public function renderHTML($node, $HTMLAttributes = []): array
    {
        return [];
    }
}
