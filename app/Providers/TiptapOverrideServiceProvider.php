<?php

namespace App\Providers;

use App\Tiptap\Nodes\SafeTiptapBlock;
use FilamentTiptapEditor\TiptapConverter;
use Illuminate\Support\ServiceProvider;

class TiptapOverrideServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend(TiptapConverter::class, function (TiptapConverter $converter, $app) {
            // Decorator pattern: we override getExtensions via anonymous subclass.
            return new class($converter) extends TiptapConverter {
                protected TiptapConverter $base;
                public function __construct(TiptapConverter $base)
                {
                    $this->base = $base; // keep original state if needed
                }
                public function getExtensions(): array
                {
                    $extensions = parent::getExtensions();
                    // Reemplaza la primera ocurrencia del nodo tiptapBlock por SafeTiptapBlock
                    foreach ($extensions as $i => $ext) {
                        if ($ext instanceof \FilamentTiptapEditor\Extensions\Nodes\TiptapBlock) {
                            // Conservamos las opciones (blocks)
                            $options = $ext->options ?? [];
                            $extensions[$i] = new SafeTiptapBlock($options);
                            break;
                        }
                    }
                    return $extensions;
                }
            };
        });
    }
}
