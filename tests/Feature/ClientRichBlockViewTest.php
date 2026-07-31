<?php

namespace Tests\Feature;

use Tests\TestCase;

class ClientRichBlockViewTest extends TestCase
{
    public function test_client_feature_preview_renders_tiptap_document_body(): void
    {
        $html = view('blocks.ClientFeature', [
            'title' => 'Título de prueba',
            'body' => [
                'type' => 'doc',
                'content' => [[
                    'type' => 'paragraph',
                    'content' => [[
                        'type' => 'text',
                        'text' => 'Contenido del preview',
                    ]],
                ]],
            ],
            'media' => [],
        ])->render();

        $this->assertStringContainsString('<p>Contenido del preview</p>', $html);
    }

    public function test_client_statement_preview_renders_tiptap_document_body(): void
    {
        $html = view('blocks.ClientStatement', [
            'title' => 'Título de prueba',
            'body' => [
                'type' => 'doc',
                'content' => [[
                    'type' => 'paragraph',
                    'content' => [[
                        'type' => 'text',
                        'text' => 'Contenido del statement',
                    ]],
                ]],
            ],
        ])->render();

        $this->assertStringContainsString('<p>Contenido del statement</p>', $html);
    }
}
