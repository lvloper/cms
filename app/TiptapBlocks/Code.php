<?php

namespace App\TiptapBlocks;

use FilamentTiptapEditor\TiptapBlock;
use Wiebenieuwenhuis\FilamentCodeEditor\Components\CodeEditor;

class Code extends TiptapBlock
{
    public string $preview = 'tiptap-blocks.previews.code';

    public string $rendered = 'tiptap-blocks.rendered.code';
    
    public ?string $icon = 'heroicon-o-code-bracket';

    public ?string $label = 'Código';

    public function getFormSchema(): array
    {
        return [
            CodeEditor::make('code')
                ->label('Código')
                ->required()
        ];
    }
}